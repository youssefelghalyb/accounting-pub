<?php

namespace Modules\Product\Services;

use Illuminate\Support\Facades\DB;
use Modules\Finance\Models\SalesInvoice;
use Modules\Finance\Services\SalesInvoiceService;
use Modules\Product\Models\Contractor;

/**
 * Gifts a contractor copies of their own book(s): a 100%-discounted sale (line_total = 0),
 * reusing SalesInvoiceService::createInvoice() verbatim for invoice/item/stock-deduction
 * creation — no separate accounting workflow, per docs/contractor-migration-plan.md §4.4.
 */
class ContractorGiftService
{
    public function __construct(
        private ContractorService $contractorService,
        private SalesInvoiceService $salesInvoiceService,
    ) {}

    /**
     * @param  array<int, array{book_id: int, product_id: int, quantity: int, unit_price: float}>  $lines
     */
    public function createGift(Contractor $contractor, array $lines, ?int $subWarehouseId = null): SalesInvoice
    {
        if (empty($lines)) {
            throw new \InvalidArgumentException('A gift must include at least one book.');
        }

        return DB::transaction(function () use ($contractor, $lines, $subWarehouseId) {
            $party = $this->contractorService->ensureParty($contractor);

            $items = array_map(function (array $line) {
                $lineTotal = $line['quantity'] * $line['unit_price'];

                return [
                    'product_id' => $line['product_id'],
                    'quantity' => $line['quantity'],
                    'unit_price' => $line['unit_price'],
                    'discount_amount' => $lineTotal, // 100% discount — line_total = 0
                    'is_gift' => true,
                ];
            }, $lines);

            return $this->salesInvoiceService->createInvoice([
                'party_id' => $party->id,
                'invoice_date' => now()->toDateString(),
                'is_taxable' => false,
                'discount_type' => 'fixed',
                'discount_value' => 0,
                'sub_warehouse_id' => $subWarehouseId,
                'items' => $items,
            ]);
        });
    }
}
