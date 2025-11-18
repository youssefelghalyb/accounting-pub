<?php

namespace Modules\Product\Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Product\Models\Author;
use Modules\Product\Models\Book;
use Modules\Product\Models\Product;
use Modules\Product\Models\Contract;
use Modules\Product\Models\ContractTransaction;
use App\Models\User;

class ContractPaymentFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate');
    }

    /** @test */
    public function it_tracks_contract_payment_status_as_pending()
    {
        $author = Author::create(['full_name' => 'John Doe']);
        $contract = Contract::create([
            'author_id' => $author->id,
            'contract_date' => '2025-01-01',
            'contract_price' => 5000.00,
        ]);

        $this->assertEquals('pending', $contract->payment_status);
        $this->assertEquals('red', $contract->status_color);
        $this->assertEquals(0.00, $contract->total_paid);
        $this->assertEquals(5000.00, $contract->outstanding_balance);
        $this->assertFalse($contract->isFullyPaid());
    }

    /** @test */
    public function it_tracks_contract_payment_status_as_partial()
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

        $contract = $contract->fresh();

        $this->assertEquals('partial', $contract->payment_status);
        $this->assertEquals('yellow', $contract->status_color);
        $this->assertEquals(2000.00, $contract->total_paid);
        $this->assertEquals(3000.00, $contract->outstanding_balance);
        $this->assertEquals(40.00, $contract->payment_percentage);
        $this->assertFalse($contract->isFullyPaid());
    }

    /** @test */
    public function it_tracks_contract_payment_status_as_paid()
    {
        $author = Author::create(['full_name' => 'John Doe']);
        $contract = Contract::create([
            'author_id' => $author->id,
            'contract_date' => '2025-01-01',
            'contract_price' => 5000.00,
        ]);

        ContractTransaction::create([
            'contract_id' => $contract->id,
            'amount' => 3000.00,
            'payment_date' => '2025-01-15',
        ]);

        ContractTransaction::create([
            'contract_id' => $contract->id,
            'amount' => 2000.00,
            'payment_date' => '2025-02-15',
        ]);

        $contract = $contract->fresh();

        $this->assertEquals('paid', $contract->payment_status);
        $this->assertEquals('green', $contract->status_color);
        $this->assertEquals(5000.00, $contract->total_paid);
        $this->assertEquals(0.00, $contract->outstanding_balance);
        $this->assertEquals(100.00, $contract->payment_percentage);
        $this->assertTrue($contract->isFullyPaid());
    }

    /** @test */
    public function it_tracks_author_total_contract_value_and_payments()
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

        ContractTransaction::create([
            'contract_id' => $contract1->id,
            'amount' => 2000.00,
            'payment_date' => '2025-01-15',
        ]);

        ContractTransaction::create([
            'contract_id' => $contract2->id,
            'amount' => 1500.00,
            'payment_date' => '2025-02-15',
        ]);

        $author = $author->fresh();

        $this->assertEquals(8000.00, $author->total_contract_value);
        $this->assertEquals(3500.00, $author->total_paid);
        $this->assertEquals(4500.00, $author->outstanding_balance);
    }

    /** @test */
    public function it_can_get_all_author_transactions_across_contracts()
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

        ContractTransaction::create([
            'contract_id' => $contract1->id,
            'amount' => 2000.00,
            'payment_date' => '2025-01-15',
        ]);

        ContractTransaction::create([
            'contract_id' => $contract1->id,
            'amount' => 1000.00,
            'payment_date' => '2025-01-25',
        ]);

        ContractTransaction::create([
            'contract_id' => $contract2->id,
            'amount' => 1500.00,
            'payment_date' => '2025-02-15',
        ]);

        $transactions = $author->getAllTransactions();

        $this->assertCount(3, $transactions);
        $this->assertEquals(4500.00, $transactions->sum('amount'));
    }

    /** @test */
    public function it_handles_contracts_without_book_id()
    {
        $author = Author::create(['full_name' => 'John Doe']);

        $contract = Contract::create([
            'author_id' => $author->id,
            'book_id' => null,
            'book_name' => 'Upcoming Novel',
            'contract_date' => '2025-01-01',
            'contract_price' => 5000.00,
        ]);

        $this->assertNull($contract->book);
        $this->assertEquals('Upcoming Novel', $contract->book_name);
        $this->assertDatabaseHas('author_book_contracts', [
            'id' => $contract->id,
            'book_id' => null,
            'book_name' => 'Upcoming Novel',
        ]);
    }

    /** @test */
    public function it_can_filter_contracts_by_author()
    {
        $author1 = Author::create(['full_name' => 'John Doe']);
        $author2 = Author::create(['full_name' => 'Jane Smith']);

        Contract::create([
            'author_id' => $author1->id,
            'contract_date' => '2025-01-01',
            'contract_price' => 5000.00,
        ]);

        Contract::create([
            'author_id' => $author1->id,
            'contract_date' => '2025-02-01',
            'contract_price' => 3000.00,
        ]);

        Contract::create([
            'author_id' => $author2->id,
            'contract_date' => '2025-01-01',
            'contract_price' => 4000.00,
        ]);

        $author1Contracts = Contract::forAuthor($author1->id)->get();

        $this->assertCount(2, $author1Contracts);
        $this->assertEquals(8000.00, $author1Contracts->sum('contract_price'));
    }

    /** @test */
    public function it_can_filter_contracts_by_book()
    {
        $author = Author::create(['full_name' => 'John Doe']);
        $product1 = Product::create(['name' => 'Book 1', 'type' => 'book']);
        $product2 = Product::create(['name' => 'Book 2', 'type' => 'book']);

        $book1 = Book::create(['product_id' => $product1->id, 'isbn' => '978-1234567890']);
        $book2 = Book::create(['product_id' => $product2->id, 'isbn' => '978-1234567891']);

        Contract::create([
            'author_id' => $author->id,
            'book_id' => $book1->id,
            'contract_date' => '2025-01-01',
            'contract_price' => 5000.00,
        ]);

        Contract::create([
            'author_id' => $author->id,
            'book_id' => $book1->id,
            'contract_date' => '2025-02-01',
            'contract_price' => 2000.00,
        ]);

        Contract::create([
            'author_id' => $author->id,
            'book_id' => $book2->id,
            'contract_date' => '2025-01-01',
            'contract_price' => 3000.00,
        ]);

        $book1Contracts = Contract::forBook($book1->id)->get();

        $this->assertCount(2, $book1Contracts);
        $this->assertEquals(7000.00, $book1Contracts->sum('contract_price'));
    }

    /** @test */
    public function it_can_filter_pending_contracts()
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

        $contract3 = Contract::create([
            'author_id' => $author->id,
            'contract_date' => '2025-03-01',
            'contract_price' => 2000.00,
        ]);

        // Add transaction to contract2
        ContractTransaction::create([
            'contract_id' => $contract2->id,
            'amount' => 1000.00,
            'payment_date' => '2025-02-15',
        ]);

        $pendingContracts = Contract::pending()->get();

        $this->assertCount(2, $pendingContracts);
        $this->assertTrue($pendingContracts->contains('id', $contract1->id));
        $this->assertTrue($pendingContracts->contains('id', $contract3->id));
        $this->assertFalse($pendingContracts->contains('id', $contract2->id));
    }

    /** @test */
    public function it_can_filter_transactions_by_date()
    {
        $author = Author::create(['full_name' => 'John Doe']);
        $contract = Contract::create([
            'author_id' => $author->id,
            'contract_date' => '2025-01-01',
            'contract_price' => 10000.00,
        ]);

        ContractTransaction::create([
            'contract_id' => $contract->id,
            'amount' => 2000.00,
            'payment_date' => now()->format('Y-m-d'),
        ]);

        ContractTransaction::create([
            'contract_id' => $contract->id,
            'amount' => 1500.00,
            'payment_date' => now()->subMonth()->format('Y-m-d'),
        ]);

        ContractTransaction::create([
            'contract_id' => $contract->id,
            'amount' => 1000.00,
            'payment_date' => now()->subYear()->format('Y-m-d'),
        ]);

        $thisMonthTransactions = ContractTransaction::thisMonth()->get();
        $thisYearTransactions = ContractTransaction::thisYear()->get();

        $this->assertCount(1, $thisMonthTransactions);
        $this->assertCount(2, $thisYearTransactions);
        $this->assertEquals(2000.00, $thisMonthTransactions->sum('amount'));
        $this->assertEquals(3500.00, $thisYearTransactions->sum('amount'));
    }

    /** @test */
    public function it_handles_overpayment_gracefully()
    {
        $author = Author::create(['full_name' => 'John Doe']);
        $contract = Contract::create([
            'author_id' => $author->id,
            'contract_date' => '2025-01-01',
            'contract_price' => 5000.00,
        ]);

        ContractTransaction::create([
            'contract_id' => $contract->id,
            'amount' => 6000.00,
            'payment_date' => '2025-01-15',
        ]);

        $contract = $contract->fresh();

        $this->assertEquals('paid', $contract->payment_status);
        $this->assertEquals(6000.00, $contract->total_paid);
        $this->assertEquals(0.00, $contract->outstanding_balance);
        $this->assertTrue($contract->isFullyPaid());
    }

    /** @test */
    public function it_tracks_creator_and_editor_for_all_models()
    {
        $user = User::factory()->create();

        $author = Author::create([
            'full_name' => 'John Doe',
            'created_by' => $user->id,
        ]);

        $contract = Contract::create([
            'author_id' => $author->id,
            'contract_date' => '2025-01-01',
            'contract_price' => 5000.00,
            'created_by' => $user->id,
        ]);

        $transaction = ContractTransaction::create([
            'contract_id' => $contract->id,
            'amount' => 2000.00,
            'payment_date' => '2025-01-15',
            'created_by' => $user->id,
        ]);

        $this->assertEquals($user->id, $author->creator->id);
        $this->assertEquals($user->id, $contract->creator->id);
        $this->assertEquals($user->id, $transaction->creator->id);
    }

    /** @test */
    public function it_supports_contract_with_profit_sharing()
    {
        $author = Author::create(['full_name' => 'John Doe']);
        $contract = Contract::create([
            'author_id' => $author->id,
            'contract_date' => '2025-01-01',
            'contract_price' => 5000.00,
            'percentage_from_book_profit' => 15.00,
        ]);

        $this->assertEquals(5000.00, $contract->contract_price);
        $this->assertEquals(15.00, $contract->percentage_from_book_profit);
        $this->assertDatabaseHas('author_book_contracts', [
            'id' => $contract->id,
            'contract_price' => 5000.00,
            'percentage_from_book_profit' => 15.00,
        ]);
    }
}
