<?php

namespace Modules\Product\Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Product\Models\ContractTransaction;
use Modules\Product\Models\Contract;
use Modules\Product\Models\Author;
use App\Models\User;

class ContractTransactionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate');
    }

    /** @test */
    public function it_can_create_a_transaction()
    {
        $author = Author::create(['full_name' => 'John Doe']);
        $contract = Contract::create([
            'author_id' => $author->id,
            'contract_date' => '2025-01-01',
            'contract_price' => 5000.00,
        ]);

        $transaction = ContractTransaction::create([
            'contract_id' => $contract->id,
            'amount' => 2000.00,
            'payment_date' => '2025-01-15',
            'notes' => 'First payment',
        ]);

        $this->assertInstanceOf(ContractTransaction::class, $transaction);
        $this->assertEquals(2000.00, $transaction->amount);
        $this->assertEquals('First payment', $transaction->notes);
    }

    /** @test */
    public function it_belongs_to_contract()
    {
        $author = Author::create(['full_name' => 'John Doe']);
        $contract = Contract::create([
            'author_id' => $author->id,
            'contract_date' => '2025-01-01',
            'contract_price' => 5000.00,
        ]);

        $transaction = ContractTransaction::create([
            'contract_id' => $contract->id,
            'amount' => 2000.00,
            'payment_date' => '2025-01-15',
        ]);

        $this->assertInstanceOf(Contract::class, $transaction->contract);
        $this->assertEquals($contract->id, $transaction->contract->id);
    }

    /** @test */
    public function it_belongs_to_creator_user()
    {
        $user = User::factory()->create();
        $author = Author::create(['full_name' => 'John Doe']);
        $contract = Contract::create([
            'author_id' => $author->id,
            'contract_date' => '2025-01-01',
            'contract_price' => 5000.00,
        ]);

        $transaction = ContractTransaction::create([
            'contract_id' => $contract->id,
            'amount' => 2000.00,
            'payment_date' => '2025-01-15',
            'created_by' => $user->id,
        ]);

        $this->assertInstanceOf(User::class, $transaction->creator);
        $this->assertEquals($user->id, $transaction->creator->id);
    }

    /** @test */
    public function it_casts_payment_date_to_date()
    {
        $author = Author::create(['full_name' => 'John Doe']);
        $contract = Contract::create([
            'author_id' => $author->id,
            'contract_date' => '2025-01-01',
            'contract_price' => 5000.00,
        ]);

        $transaction = ContractTransaction::create([
            'contract_id' => $contract->id,
            'amount' => 2000.00,
            'payment_date' => '2025-01-15',
        ]);

        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $transaction->payment_date);
    }

    /** @test */
    public function it_casts_amount_to_decimal()
    {
        $author = Author::create(['full_name' => 'John Doe']);
        $contract = Contract::create([
            'author_id' => $author->id,
            'contract_date' => '2025-01-01',
            'contract_price' => 5000.00,
        ]);

        $transaction = ContractTransaction::create([
            'contract_id' => $contract->id,
            'amount' => 2000.999,
            'payment_date' => '2025-01-15',
        ]);

        $this->assertEquals('2000.99', number_format($transaction->amount, 2));
    }

    /** @test */
    public function it_can_scope_for_contract()
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

        $transactions = ContractTransaction::forContract($contract1->id)->get();

        $this->assertCount(1, $transactions);
        $this->assertEquals($contract1->id, $transactions->first()->contract_id);
    }

    /** @test */
    public function it_can_scope_this_month()
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
            'payment_date' => now()->format('Y-m-d'),
        ]);

        ContractTransaction::create([
            'contract_id' => $contract->id,
            'amount' => 1500.00,
            'payment_date' => now()->subMonth()->format('Y-m-d'),
        ]);

        $thisMonthTransactions = ContractTransaction::thisMonth()->get();

        $this->assertCount(1, $thisMonthTransactions);
    }

    /** @test */
    public function it_can_scope_this_year()
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
            'payment_date' => now()->format('Y-m-d'),
        ]);

        ContractTransaction::create([
            'contract_id' => $contract->id,
            'amount' => 1500.00,
            'payment_date' => now()->subYear()->format('Y-m-d'),
        ]);

        $thisYearTransactions = ContractTransaction::thisYear()->get();

        $this->assertCount(1, $thisYearTransactions);
    }

    /** @test */
    public function it_has_fillable_attributes()
    {
        $fillable = [
            'contract_id', 'amount', 'payment_date', 'notes',
            'receipt_file', 'created_by', 'edited_by'
        ];

        $transaction = new ContractTransaction();

        $this->assertEquals($fillable, $transaction->getFillable());
    }
}
