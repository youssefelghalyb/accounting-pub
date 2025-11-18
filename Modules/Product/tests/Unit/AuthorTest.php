<?php

namespace Modules\Product\Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Product\Models\Author;
use Modules\Product\Models\Book;
use Modules\Product\Models\Contract;
use Modules\Product\Models\ContractTransaction;
use App\Models\User;

class AuthorTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate');
    }

    /** @test */
    public function it_can_create_an_author()
    {
        $author = Author::create([
            'full_name' => 'John Doe',
            'nationality' => 'American',
            'email' => 'john@example.com',
        ]);

        $this->assertInstanceOf(Author::class, $author);
        $this->assertEquals('John Doe', $author->full_name);
        $this->assertEquals('American', $author->nationality);
    }

    /** @test */
    public function it_has_books_relationship()
    {
        $author = Author::create(['full_name' => 'John Doe']);

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $author->books());
    }

    /** @test */
    public function it_has_contracts_relationship()
    {
        $author = Author::create(['full_name' => 'John Doe']);

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $author->contracts());
    }

    /** @test */
    public function it_belongs_to_creator_user()
    {
        $user = User::factory()->create();
        $author = Author::create([
            'full_name' => 'John Doe',
            'created_by' => $user->id,
        ]);

        $this->assertInstanceOf(User::class, $author->creator);
        $this->assertEquals($user->id, $author->creator->id);
    }

    /** @test */
    public function it_calculates_total_contract_value()
    {
        $author = Author::create(['full_name' => 'John Doe']);

        Contract::create([
            'author_id' => $author->id,
            'contract_date' => '2025-01-01',
            'contract_price' => 5000.00,
        ]);

        Contract::create([
            'author_id' => $author->id,
            'contract_date' => '2025-02-01',
            'contract_price' => 3000.00,
        ]);

        $this->assertEquals(8000.00, $author->total_contract_value);
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

        // Refresh to get updated relations
        $author = $author->fresh();

        $this->assertEquals(3500.00, $author->total_paid);
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

        // Refresh to get updated relations
        $author = $author->fresh();

        $this->assertEquals(3000.00, $author->outstanding_balance);
    }

    /** @test */
    public function it_can_get_all_transactions()
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

        $transactions = $author->getAllTransactions();

        $this->assertCount(2, $transactions);
    }

    /** @test */
    public function it_has_appended_attributes()
    {
        $author = Author::create(['full_name' => 'John Doe']);

        $array = $author->toArray();

        $this->assertArrayHasKey('total_contract_value', $array);
        $this->assertArrayHasKey('total_paid', $array);
        $this->assertArrayHasKey('outstanding_balance', $array);
    }

    /** @test */
    public function it_has_fillable_attributes()
    {
        $fillable = [
            'full_name', 'nationality', 'country_of_residence', 'bio',
            'occupation', 'phone_number', 'whatsapp_number', 'email',
            'id_image', 'created_by', 'edited_by'
        ];

        $author = new Author();

        $this->assertEquals($fillable, $author->getFillable());
    }
}
