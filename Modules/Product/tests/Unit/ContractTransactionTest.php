<?php

namespace Modules\Product\Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Modules\Product\Models\Book;
use Modules\Product\Models\Contractor;
use Modules\Product\Models\ContractorBook;
use Modules\Product\Models\ContractTransaction;
use Modules\Product\Models\Product;
use Tests\TestCase;

/**
 * The exactly-one-voucher invariant (docs/contractor-migration-plan.md §4.2b) — every
 * ContractTransaction must reference exactly one of receipt_voucher_id / payment_voucher_id.
 * Exercised through the Eloquent model, since that's the only enforcement SQLite gets
 * (the DB-level CHECK constraint isn't supported there — see the migration's docblock).
 */
class ContractTransactionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate');
    }

    private function makeContractorBook(): ContractorBook
    {
        $product = Product::create(['name' => 'Test Book', 'type' => 'book', 'base_price' => 50]);
        $book = Book::create(['product_id' => $product->id, 'isbn' => uniqid('isbn-'), 'cover_type' => 'soft']);
        $contractor = Contractor::create(['name' => 'Test Contractor']);

        return ContractorBook::create([
            'book_id' => $book->id,
            'contractor_id' => $contractor->id,
            'profit_percentage' => 10,
            'percentage_basis' => 'sale_price',
        ]);
    }

    /** @test */
    public function it_rejects_a_transaction_with_neither_voucher()
    {
        $this->expectException(\LogicException::class);

        ContractTransaction::create([
            'contractor_book_id' => $this->makeContractorBook()->id,
            'type' => 'adjustment',
            'amount' => 10,
            'transaction_date' => now(),
        ]);
    }

    /** @test */
    public function it_rejects_a_transaction_with_both_vouchers()
    {
        $this->expectException(\LogicException::class);

        ContractTransaction::create([
            'contractor_book_id' => $this->makeContractorBook()->id,
            'type' => 'adjustment',
            'amount' => 10,
            'transaction_date' => now(),
            'receipt_voucher_id' => 1,
            'payment_voucher_id' => 1,
        ]);
    }

    /** @test */
    public function direction_is_derived_from_the_voucher_link()
    {
        $partyId = DB::table('parties')->insertGetId(['name' => 'Test Party', 'created_at' => now(), 'updated_at' => now()]);
        $accountId = DB::table('accounts')->insertGetId(['account_name' => 'Test Account', 'created_at' => now(), 'updated_at' => now()]);

        $receiptVoucherId = DB::table('receipt_vouchers')->insertGetId([
            'voucher_number' => 'RV-TEST-1', 'party_id' => $partyId, 'account_id' => $accountId,
            'amount' => 10, 'voucher_date' => now(), 'created_at' => now(), 'updated_at' => now(),
        ]);
        $paymentVoucherId = DB::table('payment_vouchers')->insertGetId([
            'voucher_number' => 'PV-TEST-1', 'party_id' => $partyId, 'account_id' => $accountId,
            'amount' => 10, 'voucher_date' => now(), 'created_at' => now(), 'updated_at' => now(),
        ]);

        $incoming = ContractTransaction::create([
            'contractor_book_id' => $this->makeContractorBook()->id,
            'type' => 'publishing_fee',
            'amount' => 10,
            'transaction_date' => now(),
            'receipt_voucher_id' => $receiptVoucherId,
        ]);
        $this->assertEquals('in', $incoming->direction);

        $outgoing = ContractTransaction::create([
            'contractor_book_id' => $this->makeContractorBook()->id,
            'type' => 'royalty_payment',
            'amount' => 10,
            'transaction_date' => now(),
            'payment_voucher_id' => $paymentVoucherId,
        ]);
        $this->assertEquals('out', $outgoing->direction);
    }
}
