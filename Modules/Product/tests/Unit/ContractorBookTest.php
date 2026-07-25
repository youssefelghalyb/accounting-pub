<?php

namespace Modules\Product\Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Product\Models\Book;
use Modules\Product\Models\Contractor;
use Modules\Product\Models\ContractorBook;
use Modules\Product\Models\Product;
use Tests\TestCase;

class ContractorBookTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate');
    }

    private function makeBook(): Book
    {
        $product = Product::create(['name' => 'Test Book', 'type' => 'book', 'base_price' => 50]);

        return Book::create(['product_id' => $product->id, 'isbn' => uniqid('isbn-'), 'cover_type' => 'soft']);
    }

    private function makeContractorBook(string $basis, float $percentage, ?Book $book = null): ContractorBook
    {
        $contractor = Contractor::create(['name' => 'Test Contractor']);
        $book ??= $this->makeBook();

        return ContractorBook::create([
            'book_id' => $book->id,
            'contractor_id' => $contractor->id,
            'profit_percentage' => $percentage,
            'percentage_basis' => $basis,
        ]);
    }

    /** @test */
    public function it_calculates_profit_on_sale_price_basis()
    {
        $contractorBook = $this->makeContractorBook('sale_price', 20);

        // 60 (sale price) * 3 (qty) * 20% = 36
        $this->assertEquals(36.0, $contractorBook->calculateProfit(salePrice: 60, basePrice: 100, quantity: 3));
    }

    /** @test */
    public function it_calculates_profit_on_base_price_basis()
    {
        $contractorBook = $this->makeContractorBook('base_price', 15);

        // 100 (base price) * 2 (qty) * 15% = 30, unaffected by the discounted sale price
        $this->assertEquals(30.0, $contractorBook->calculateProfit(salePrice: 40, basePrice: 100, quantity: 2));
    }

    /** @test */
    public function it_defaults_quantity_to_one()
    {
        $contractorBook = $this->makeContractorBook('sale_price', 10);

        $this->assertEquals(5.0, $contractorBook->calculateProfit(salePrice: 50, basePrice: 50));
    }

    /** @test */
    public function it_enforces_one_contractor_per_book()
    {
        $contractorBook = $this->makeContractorBook('sale_price', 10);

        $contractor2 = Contractor::create(['name' => 'Second Contractor']);

        $this->expectException(\Illuminate\Database\QueryException::class);

        ContractorBook::create([
            'book_id' => $contractorBook->book_id,
            'contractor_id' => $contractor2->id,
            'profit_percentage' => 5,
            'percentage_basis' => 'sale_price',
        ]);
    }
}
