<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;

/**
 * SearchSelectController
 *
 * A single generic controller that powers all <x-searchable-select> inputs.
 * Each "resource" is registered in the $resources map below.
 *
 * Route (add to routes/web.php):
 *   Route::get('/search-select/{resource}', [SearchSelectController::class, 'search'])
 *        ->name('search-select')
 *        ->middleware('auth');
 */
class SearchSelectController extends Controller
{
    /**
     * Registry of searchable resources.
     *
     * Each entry defines:
     *   model       - fully-qualified Eloquent model class
     *   labelColumn - column used as display text  (supports dot notation for relations)
     *   searchColumns - columns to search in
     *   sublabel    - optional extra info shown under the label (dot notation OK)
     *   scopes      - optional array of local scopes to apply (no arguments)
     *   filters     - optional allowed extra filter params mapped to column
     *   gate        - optional Gate ability required to access this resource
     *   orderBy     - column to sort by (default: id)
     */
    protected array $resources = [

        'parties' => [
            'model' => \Modules\Finance\Models\Party::class,
            'labelColumn' => 'name',
            'searchColumns' => ['name', 'phone', 'email'],
            'sublabel' => 'phone',           // shown under name
            'scopes' => ['active'],
            'filters' => [],
            'orderBy' => 'name',
        ],

        'sales-invoices' => [
            'model' => \Modules\Finance\Models\SalesInvoice::class,
            'labelColumn' => 'invoice_number',
            'searchColumns' => ['invoice_number'],
            'sublabel' => 'party.name',  // model attribute / accessor
            'scopes' => [],
            'filters' => [
                'party_id' => 'party_id',             // ?party_id=X will filter
                'status' => 'status',
            ],
            'orderBy' => 'invoice_date',
            'orderDir' => 'desc',
        ],


         'purchase-invoices' => [
            'model' => \Modules\Finance\Models\PurchaseInvoice::class,
            'labelColumn' => 'invoice_number',
            'searchColumns' => ['invoice_number'],
            'sublabel' => 'party.name',  // model attribute / accessor
            'scopes' => [],
            'filters' => [
                'party_id' => 'party_id',             // ?party_id=X will filter
                'status' => 'status',
            ],
            'orderBy' => 'invoice_date',
            'orderDir' => 'desc',
        ],


        'accounts' => [
            'model' => \Modules\Finance\Models\Account::class,
            'labelColumn' => 'display_name',
            'searchColumns' => ['name', 'code'],
            'sublabel' => 'current_balance',
            'scopes' => ['active'],
            'filters' => [],
            'orderBy' => 'name',
        ],

        'employees' => [
            'model' => \Modules\HR\Models\Employee::class,
            'labelColumn' => 'full_name',
            'searchColumns' => ['first_name', 'last_name', 'employee_code'],
            'sublabel' => 'department.name',
            'scopes' => ['active'],
            'filters' => ['department_id' => 'department_id'],
            'orderBy' => 'first_name',
        ],

        'departments' => [
            'model' => \Modules\HR\Models\Department::class,
            'labelColumn' => 'name',
            'searchColumns' => ['name'],
            'scopes' => ['active'],
            'filters' => [],
            'orderBy' => 'name',
        ],

        'books' => [
            'model' => \Modules\Product\Models\Book::class,
            'labelColumn' => 'name',
            'searchColumns' => ['name', 'isbn', 'sku'],
            'sublabel' => 'isbn',
            'scopes' => ['active'],
            'filters' => [
                'category_id' => 'category_id',
                'sub_category_id' => 'sub_category_id',
                'author_id' => 'author_id',
            ],
            'orderBy' => 'name',
        ],

        'warehouses' => [
            'model' => \Modules\Warehouse\Models\Warehouse::class,
            'labelColumn' => 'name',
            'searchColumns' => ['name'],
            'scopes' => ['active'],
            'filters' => [],
            'orderBy' => 'name',
        ],

        'sub-warehouses' => [
            'model' => \Modules\Warehouse\Models\SubWarehouse::class,
            'labelColumn' => 'name',
            'searchColumns' => ['name'],
            'sublabel' => 'warehouse.name',
            'scopes' => ['active'],
            'filters' => ['warehouse_id' => 'warehouse_id'],
            'orderBy' => 'name',
        ],
    ];

    /**
     * GET /search-select/{resource}
     *
     * Query params:
     *   q        - search term
     *   page     - current page  (default 1)
     *   per_page - items per page (default 10, max 50)
     *   + any filter keys defined in the resource's 'filters' map
     */
    public function search(Request $request, string $resource): JsonResponse
    {
        abort_unless(array_key_exists($resource, $this->resources), 404);

        $config = $this->resources[$resource];

        // Gate check
        if (!empty($config['gate'])) {
            abort_unless(Gate::allows($config['gate']), 403);
        }

        $modelClass = $config['model'];
        $labelColumn = $config['labelColumn'];
        $searchColumns = $config['searchColumns'] ?? [$labelColumn];
        $scopes = $config['scopes'] ?? [];
        $filters = $config['filters'] ?? [];
        $orderBy = $config['orderBy'] ?? 'id';
        $orderDir = $config['orderDir'] ?? 'asc';
        $sublabelKey = $config['sublabel'] ?? null;

        $perPage = min((int) $request->input('per_page', 10), 50);
        $query = trim($request->input('q', ''));

        /** @var \Illuminate\Database\Eloquent\Builder $builder */
        $builder = $modelClass::query();

        // Apply scopes
        foreach ($scopes as $scope) {
            $builder->{$scope}();
        }

        // Apply allowed filters
        foreach ($filters as $param => $column) {
            if ($request->filled($param)) {
                $builder->where($column, $request->input($param));
            }
        }

        // Apply search
        if ($query !== '') {
            $builder->where(function ($q) use ($searchColumns, $query) {
                foreach ($searchColumns as $i => $col) {
                    $method = $i === 0 ? 'where' : 'orWhere';
                    $q->{$method}($col, 'like', "%{$query}%");
                }
            });
        }

        $paginated = $builder
            ->orderBy($orderBy, $orderDir)
            ->paginate($perPage);

        // Transform items
        $items = $paginated->getCollection()->map(function ($model) use ($labelColumn, $sublabelKey) {
            $item = [
                'id' => $model->getKey(),
                'text' => $this->resolveValue($model, $labelColumn),
            ];

            if ($sublabelKey) {
                $sublabelValue = $this->resolveValue($model, $sublabelKey);

                // Format numbers nicely
                if (is_numeric($sublabelValue)) {
                    $sublabelValue = number_format((float) $sublabelValue, 2);
                }

                $item['sublabel'] = $sublabelValue;
            }

            return $item;
        });

        return response()->json([
            'data' => $items,
            'meta' => [
                'current_page' => $paginated->currentPage(),
                'last_page' => $paginated->lastPage(),
                'total' => $paginated->total(),
                'per_page' => $paginated->perPage(),
            ],
        ]);
    }

    /**
     * Resolve a value from a model using dot notation for relations.
     * e.g. 'department.name' → $model->department->name
     */
    protected function resolveValue($model, string $key): mixed
    {
        if (!str_contains($key, '.')) {
            return $model->{$key} ?? null;
        }

        $parts = explode('.', $key);
        $value = $model;
        foreach ($parts as $part) {
            if ($value === null)
                return null;
            $value = is_array($value) ? ($value[$part] ?? null) : ($value->{$part} ?? null);
        }

        return $value;
    }
}