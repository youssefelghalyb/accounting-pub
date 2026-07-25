<?php

namespace Modules\Product\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Modules\Finance\Services\SalesInvoiceService;
use Modules\Product\Models\Book;
use Modules\Product\Models\Contractor;
use Modules\Product\Models\ContractorBook;
use Modules\Product\Models\Product;
use Tests\TestCase;

/**
 * BookSaleService's wiring into Finance's SalesInvoiceService (the only place BookSale rows
 * get created — docs/contractor-migration-plan.md §4.3), exercised at the real service level
 * rather than unit-testing BookSaleService in isolation.
 */
class SalesInvoiceBookSaleFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate');
    }

    private function makePartyId(): int
    {
        return DB::table('parties')->insertGetId([
            'name' => 'Test Customer', 'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    private function makeBookWithContractor(float $basePrice, float $percentage, string $basis = 'sale_price'): Product
    {
        $product = Product::create(['name' => 'Royalty Book', 'type' => 'book', 'base_price' => $basePrice]);
        $book = Book::create(['product_id' => $product->id, 'isbn' => uniqid('isbn-'), 'cover_type' => 'soft']);
        $contractor = Contractor::create(['name' => 'Royalty Contractor']);

        ContractorBook::create([
            'book_id' => $book->id,
            'contractor_id' => $contractor->id,
            'profit_percentage' => $percentage,
            'percentage_basis' => $basis,
        ]);

        return $product;
    }

    /** @test */
    public function creating_an_invoice_records_a_book_sale_with_the_correct_profit()
    {
        $product = $this->makeBookWithContractor(basePrice: 50, percentage: 20);

        $invoice = app(SalesInvoiceService::class)->createInvoice([
            'party_id' => $this->makePartyId(),
            'invoice_date' => now()->toDateString(),
            'is_taxable' => false,
            'discount_type' => 'fixed',
            'discount_value' => 0,
            'items' => [
                ['product_id' => $product->id, 'quantity' => 4, 'unit_price' => 60],
            ],
        ]);

        $sale = DB::table('book_sales')->where('invoice_id', $invoice->id)->first();

        $this->assertNotNull($sale);
        $this->assertEquals(4, $sale->quantity);
        $this->assertEquals(0, $sale->is_gift);
        // sale_price basis: 60 * 4 * 20% = 48
        $this->assertEquals(48.0, (float) $sale->contractor_profit);
    }

    /** @test */
    public function a_gift_line_item_records_zero_profit()
    {
        $product = $this->makeBookWithContractor(basePrice: 50, percentage: 20);

        $invoice = app(SalesInvoiceService::class)->createInvoice([
            'party_id' => $this->makePartyId(),
            'invoice_date' => now()->toDateString(),
            'is_taxable' => false,
            'discount_type' => 'fixed',
            'discount_value' => 0,
            'items' => [
                ['product_id' => $product->id, 'quantity' => 1, 'unit_price' => 50, 'discount_amount' => 50, 'is_gift' => true],
            ],
        ]);

        $sale = DB::table('book_sales')->where('invoice_id', $invoice->id)->first();

        $this->assertEquals(1, $sale->is_gift);
        $this->assertEquals(0.0, (float) $sale->contractor_profit);
    }

    /** @test */
    public function a_book_with_no_contractor_produces_no_book_sale()
    {
        $product = Product::create(['name' => 'No Contractor Book', 'type' => 'book', 'base_price' => 50]);
        Book::create(['product_id' => $product->id, 'isbn' => uniqid('isbn-'), 'cover_type' => 'soft']);

        $invoice = app(SalesInvoiceService::class)->createInvoice([
            'party_id' => $this->makePartyId(),
            'invoice_date' => now()->toDateString(),
            'is_taxable' => false,
            'discount_type' => 'fixed',
            'discount_value' => 0,
            'items' => [
                ['product_id' => $product->id, 'quantity' => 1, 'unit_price' => 50],
            ],
        ]);

        $this->assertEquals(0, DB::table('book_sales')->where('invoice_id', $invoice->id)->count());
    }

    /** @test */
    public function updating_an_invoice_replaces_the_book_sale_instead_of_duplicating_it()
    {
        $product = $this->makeBookWithContractor(basePrice: 50, percentage: 20);
        $partyId = $this->makePartyId();

        $invoice = app(SalesInvoiceService::class)->createInvoice([
            'party_id' => $partyId,
            'invoice_date' => now()->toDateString(),
            'is_taxable' => false,
            'discount_type' => 'fixed',
            'discount_value' => 0,
            'items' => [
                ['product_id' => $product->id, 'quantity' => 4, 'unit_price' => 60],
            ],
        ]);

        app(SalesInvoiceService::class)->updateInvoice($invoice->fresh(), [
            'party_id' => $partyId,
            'invoice_date' => now()->toDateString(),
            'is_taxable' => false,
            'discount_type' => 'fixed',
            'discount_value' => 0,
            'items' => [
                ['product_id' => $product->id, 'quantity' => 2, 'unit_price' => 60],
            ],
        ]);

        $sales = DB::table('book_sales')->where('invoice_id', $invoice->id)->get();

        $this->assertCount(1, $sales);
        $this->assertEquals(2, $sales->first()->quantity);
        // 60 * 2 * 20% = 24
        $this->assertEquals(24.0, (float) $sales->first()->contractor_profit);
    }
}
