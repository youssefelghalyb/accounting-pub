<?php

namespace Modules\Product\Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Product\Models\Contract;
use Modules\Product\Models\Author;
use Modules\Product\Models\Book;
use Modules\Product\Models\Product;
use Modules\Product\Models\ContractTransaction;
use App\Models\User;

class ContractTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate');
    }

    /** @test */
    public function it_can_create_a_contract()
    {
        $author = Author::create(['full_name' => 'John Doe']);

        $contract = Contract::create([
            'author_id' => $author->id,
            'contract_date' => '2025-01-01',
            'contract_price' => 5000.00,
            'percentage_from_book_profit' => 15.00,
        ]);

        $this->assertInstanceOf(Contract::class, $contract);
        $this->assertEquals(5000.00, $contract->contract_price);
        $this->assertEquals(15.00, $contract->percentage_from_book_profit);
    }

    /** @test */
    public function it_belongs_to_author()
    {
        $author = Author::create(['full_name' => 'John Doe']);
        $contract = Contract::create([
            'author_id' => $author->id,
            'contract_date' => '2025-01-01',
            'contract_price' => 5000.00,
        ]);

        $this->assertInstanceOf(Author::class, $contract->author);
        $this->assertEquals('John Doe', $contract->author->full_name);
    }

    /** @test */
    public function it_belongs_to_book()
    {
        $product = Product::create(['name' => 'Test Book', 'type' => 'book']);
        $author = Author::create(['full_name' => 'John Doe']);
        $book = Book::create([
            'product_id' => $product->id,
            'author_id' => $author->id,
            'isbn' => '978-1234567890',
        ]);

        $contract = Contract::create([
            'author_id' => $author->id,
            'book_id' => $book->id,
            'contract_date' => '2025-01-01',
            'contract_price' => 5000.00,
        ]);

        $this->assertInstanceOf(Book::class, $contract->book);
        $this->assertEquals($book->id, $contract->book->id);
    }

    /** @test */
    public function it_has_transactions_relationship()
    {
        $author = Author::create(['full_name' => 'John Doe']);
        $contract = Contract::create([
            'author_id' => $author->id,
            'contract_date' => '2025-01-01',
            'contract_price' => 5000.00,
        ]);

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $contract->transactions());
    }

    /** @test */
    public function it_calculates_total_paid()
    {
        $author = Author::create(['full_name' => 'John Doe']);
        $contract = Contract::create([
            'author_id' => $author->id,
            'contract_date' => '2025-01-01',
            'contract_price' => 5000.00,
        ]);

        ContractTransaction::create([
            'contract_id' => $contract->id,
            'amount' => 2000.00,
            'payment_date' => '2025-01-15',
        ]);

        ContractTransaction::create([
            'contract_id' => $contract->id,
            'amount' => 1500.00,
            'payment_date' => '2025-02-15',
        ]);

        $this->assertEquals(3500.00, $contract->total_paid);
    }

    /** @test */
    public function it_calculates_outstanding_balance()
    {
        $author = Author::create(['full_name' => 'John Doe']);
        $contract = Contract::create([
            'author_id' => $author->id,
            'contract_date' => '2025-01-01',
            'contract_price' => 5000.00,
        ]);

        ContractTransaction::create([
            'contract_id' => $contract->id,
            'amount' => 2000.00,
            'payment_date' => '2025-01-15',
        ]);

        $this->assertEquals(3000.00, $contract->outstanding_balance);
    }

    /** @test */
    public function it_calculates_payment_percentage()
    {
        $author = Author::create(['full_name' => 'John Doe']);
        $contract = Contract::create([
            'author_id' => $author->id,
            'contract_date' => '2025-01-01',
            'contract_price' => 5000.00,
        ]);

        ContractTransaction::create([
            'contract_id' => $contract->id,
            'amount' => 2500.00,
            'payment_date' => '2025-01-15',
        ]);

        $this->assertEquals(50.00, $contract->payment_percentage);
    }

    /** @test */
    public function it_returns_pending_payment_status_when_no_transactions()
    {
        $author = Author::create(['full_name' => 'John Doe']);
        $contract = Contract::create([
            'author_id' => $author->id,
            'contract_date' => '2025-01-01',
            'contract_price' => 5000.00,
        ]);

        $this->assertEquals('pending', $contract->payment_status);
    }

    /** @test */
    public function it_returns_partial_payment_status_when_partially_paid()
    {
        $author = Author::create(['full_name' => 'John Doe']);
        $contract = Contract::create([
            'author_id' => $author->id,
            'contract_date' => '2025-01-01',
            'contract_price' => 5000.00,
        ]);

        ContractTransaction::create([
            'contract_id' => $contract->id,
            'amount' => 2000.00,
            'payment_date' => '2025-01-15',
        ]);

        $this->assertEquals('partial', $contract->payment_status);
    }

    /** @test */
    public function it_returns_paid_payment_status_when_fully_paid()
    {
        $author = Author::create(['full_name' => 'John Doe']);
        $contract = Contract::create([
            'author_id' => $author->id,
            'contract_date' => '2025-01-01',
            'contract_price' => 5000.00,
        ]);

        ContractTransaction::create([
            'contract_id' => $contract->id,
            'amount' => 5000.00,
            'payment_date' => '2025-01-15',
        ]);

        $this->assertEquals('paid', $contract->payment_status);
        $this->assertTrue($contract->isFullyPaid());
    }

    /** @test */
    public function it_returns_status_color_based_on_payment_status()
    {
        $author = Author::create(['full_name' => 'John Doe']);
        $contract = Contract::create([
            'author_id' => $author->id,
            'contract_date' => '2025-01-01',
            'contract_price' => 5000.00,
        ]);

        // Pending
        $this->assertEquals('red', $contract->status_color);

        // Partial
        ContractTransaction::create([
            'contract_id' => $contract->id,
            'amount' => 2000.00,
            'payment_date' => '2025-01-15',
        ]);
        $contract = $contract->fresh();
        $this->assertEquals('yellow', $contract->status_color);

        // Paid
        ContractTransaction::create([
            'contract_id' => $contract->id,
            'amount' => 3000.00,
            'payment_date' => '2025-02-15',
        ]);
        $contract = $contract->fresh();
        $this->assertEquals('green', $contract->status_color);
    }

    /** @test */
    public function it_can_scope_for_author()
    {
        $author1 = Author::create(['full_name' => 'John Doe']);
        $author2 = Author::create(['full_name' => 'Jane Smith']);

        Contract::create([
            'author_id' => $author1->id,
            'contract_date' => '2025-01-01',
            'contract_price' => 5000.00,
        ]);

        Contract::create([
            'author_id' => $author2->id,
            'contract_date' => '2025-01-01',
            'contract_price' => 3000.00,
        ]);

        $contracts = Contract::forAuthor($author1->id)->get();

        $this->assertCount(1, $contracts);
        $this->assertEquals($author1->id, $contracts->first()->author_id);
    }

    /** @test */
    public function it_can_scope_pending_contracts()
    {
        $author = Author::create(['full_name' => 'John Doe']);

        $contract1 = Contract::create([
            'author_id' => $author->id,
            'contract_date' => '2025-01-01',
            'contract_price' => 5000.00,
        ]);

        $contract2 = Contract::create([
            'author_id' => $author->id,
            'contract_date' => '2025-02-01',
            'contract_price' => 3000.00,
        ]);

        // Add transaction to contract2
        ContractTransaction::create([
            'contract_id' => $contract2->id,
            'amount' => 1000.00,
            'payment_date' => '2025-02-15',
        ]);

        $pendingContracts = Contract::pending()->get();

        $this->assertCount(1, $pendingContracts);
        $this->assertEquals($contract1->id, $pendingContracts->first()->id);
    }

    /** @test */
    public function it_has_fillable_attributes()
    {
        $fillable = [
            'author_id', 'book_id', 'contract_date', 'contract_price',
            'percentage_from_book_profit', 'contract_file', 'created_by',
            'edited_by', 'book_name'
        ];

        $contract = new Contract();

        $this->assertEquals($fillable, $contract->getFillable());
    }
}
