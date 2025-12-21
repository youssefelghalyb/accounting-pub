<?php

namespace Modules\Customer\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Customer\app\Http\Requests\StoreCustomerRequest;
use Modules\Customer\Http\Requests\UpdateCustomerRequest;
use Modules\Customer\Models\Customer;

class CustomerController extends Controller
{
    /**
     * Display a listing of customers.
     */
    public function index(Request $request)
    {
        $query = Customer::query();

        // Search functionality
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        // Filter by type
        if ($request->filled('type')) {
            $query->ofType($request->type);
        }

        // Filter by status
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->active();
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        $customers = $query->orderBy('created_at', 'desc')->get();

        // Calculate statistics
        $stats = [
            'total_customers' => Customer::count(),
            'active_customers' => Customer::active()->count(),
            'individual_customers' => Customer::ofType('individual')->count(),
            'company_customers' => Customer::ofType('company')->count(),
            'online_customers' => Customer::ofType('online')->count(),
        ];

        return view('customer::customers.index', compact('customers', 'stats'));
    }

    /**
     * Show the form for creating a new customer.
     */
    public function create()
    {
        return view('customer::customers.create');
    }

    /**
     * Store a newly created customer.
     */
    public function store(StoreCustomerRequest $request)
    {
        $validated = $request->validated();

        Customer::create($validated);

        return redirect()
            ->route('customer.customers.index')
            ->with('success', __('customer::customer.customer_added'));
    }

    /**
     * Display the specified customer.
     */
        public function show($id)
    {
        $customer = Customer::findOrFail($id);
        
        return view('customer::customers.show', compact('customer'));
    }


    /**
     * Show the form for editing the specified customer.
     */
    public function edit($id)
    {
        $customer = Customer::findOrFail($id);
        
        return view('customer::customers.edit', compact('customer'));
    }

    /**
     * Update the specified customer.
     */
    public function update(UpdateCustomerRequest $request, $id)
    {
        $customer = Customer::findOrFail($id);
        $validated = $request->validated();

        $customer->update($validated);

        return redirect()
            ->route('customer.customers.index')
            ->with('success', __('customer::customer.customer_updated'));
    }

    /**
     * Remove the specified customer.
     */
    public function destroy(Customer $customer)
    {
        // Check if customer has any orders
        if ($customer->orders()->exists()) {
            return redirect()
                ->route('customer.customers.index')
                ->with('error', __('customer::customer.cannot_delete_has_orders'));
        }

        $customer->delete();

        return redirect()
            ->route('customer.customers.index')
            ->with('success', __('customer::customer.customer_deleted'));
    }

    /**
     * Toggle customer active status
     */
    public function toggleStatus(Customer $customer)
    {
        if ($customer->is_active) {
            $customer->deactivate();
            $message = __('customer::customer.customer_deactivated');
        } else {
            $customer->activate();
            $message = __('customer::customer.customer_activated');
        }

        return redirect()
            ->back()
            ->with('success', $message);
    }
}