<?php

namespace Modules\Finance\Services;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Modules\Finance\Models\Party;

class PartyService
{
    // ==================== Party CRUD ====================

    public function createParty(array $data): Party
    {
        return DB::transaction(function () use ($data) {
            return Party::create([
                ...$data,
                'created_by' => Auth::id(),
            ]);
        });
    }

    public function updateParty(Party $party, array $data): Party
    {
        return DB::transaction(function () use ($party, $data) {
            $party->update([
                ...$data,
                'edited_by' => Auth::id(),
            ]);

            return $party->refresh();
        });
    }

    public function deleteParty(Party $party): bool
    {
        if ($this->hasTransactions($party)) {
            throw new \Exception(__('finance::party.cannot_delete_has_transactions'));
        }

        return $party->delete();
    }

    public function toggleStatus(Party $party): Party
    {
        return DB::transaction(function () use ($party) {
            $party->update([
                'is_active' => ! $party->is_active,
                'edited_by' => Auth::id(),
            ]);

            return $party->refresh();
        });
    }

    // ==================== Statistics ====================

    /**
     * Global party statistics resolved in 2 DB queries — no model hydration.
     */
    public function getStatistics(): array
    {
        $counts = $this->getPartyCounts();
        $financials = $this->getPartyFinancials();

        return [
            'total_parties'     => $counts->total_parties,
            'active_parties'    => $counts->active_parties,
            'total_customers'   => $financials->total_customers,
            'total_vendors'     => $financials->total_vendors,
            'total_receivables' => round((float) $financials->total_receivables, 2),
            'total_payables'    => round((float) $financials->total_payables, 2),
        ];
    }

    /**
     * Financial summary for a single party.
     * Pass a party pre-loaded with withSum aggregates for zero extra queries.
     * Falls back to direct queries automatically via model accessors.
     */
    public function getPartySummary(Party $party): array
    {
        return [
            'total_sales'             => $party->total_sales,
            'total_payments_received' => $party->total_payments_received,
            'customer_balance'        => $party->customer_balance,
            'vendor_balance'          => $party->vendor_balance,
            'unpaid_invoices_count'   => $party->unpaid_invoices_count,
            'paid_invoices_count'     => $party->paid_invoices_count,
        ];
    }

    // ==================== Helpers ====================

    /**
     * Load a single party with all withSum aggregates pre-loaded.
     * Use this in show() / edit() to avoid N+1 on accessor calls.
     */
    public function findWithAggregates(int $id): Party
    {
        return Party::withSum('salesInvoices as total_sales', 'total_amount')
            ->withSum('receiptVouchers as total_receipts', 'amount')
            ->withSum('purchaseInvoices as total_purchases', 'total_amount')
            ->withSum('paymentVouchers as total_payments', 'amount')
            ->findOrFail($id);
    }

    private function hasTransactions(Party $party): bool
    {
        return $party->salesInvoices()->exists()
            || $party->receiptVouchers()->exists()
            || $party->purchaseInvoices()->exists()
            || $party->paymentVouchers()->exists();
    }

    /**
     * Query 1 — simple counts, no joins needed.
     */
    private function getPartyCounts(): object
    {
        return DB::table('parties')
            ->selectRaw('
                COUNT(*)                                        AS total_parties,
                SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) AS active_parties
            ')
            ->first();
    }

    /**
     * Query 2 — all financial aggregates via pre-aggregated subquery joins.
     * Avoids row multiplication that straight LEFT JOINs on multiple has-many tables produce.
     */
    private function getPartyFinancials(): object
    {
        return DB::table('parties')
            ->selectRaw('
                COUNT(DISTINCT si.party_id)                                             AS total_customers,
                COUNT(DISTINCT pi.party_id)                                             AS total_vendors,
                COALESCE(SUM(si.total), 0) - COALESCE(SUM(rv.total), 0)
                    - (COALESCE(SUM(pi.total), 0) - COALESCE(SUM(pv.total), 0))        AS total_receivables,
                COALESCE(SUM(pi.total), 0) - COALESCE(SUM(pv.total), 0)                AS total_payables
            ')
            ->leftJoin(
                DB::raw('(SELECT party_id, SUM(total_amount) AS total FROM sales_invoices    GROUP BY party_id) si'),
                'si.party_id', '=', 'parties.id'
            )
            ->leftJoin(
                DB::raw('(SELECT party_id, SUM(amount)       AS total FROM receipt_vouchers  GROUP BY party_id) rv'),
                'rv.party_id', '=', 'parties.id'
            )
            ->leftJoin(
                DB::raw('(SELECT party_id, SUM(total_amount) AS total FROM purchase_invoices GROUP BY party_id) pi'),
                'pi.party_id', '=', 'parties.id'
            )
            ->leftJoin(
                DB::raw('(SELECT party_id, SUM(amount)       AS total FROM payment_vouchers  GROUP BY party_id) pv'),
                'pv.party_id', '=', 'parties.id'
            )
            ->first();
    }
}