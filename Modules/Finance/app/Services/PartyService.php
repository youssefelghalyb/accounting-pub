<?php

namespace Modules\Finance\Services;

use Modules\Finance\Models\Party;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PartyService
{
    /**
     * Create a new party
     */
    public function createParty(array $data): Party
    {
        return DB::transaction(function () use ($data) {
            $data['created_by'] = Auth::id();
            return Party::create($data);
        });
    }

    /**
     * Update an existing party
     */
    public function updateParty(Party $party, array $data): Party
    {
        DB::transaction(function () use ($party, $data) {
            $data['edited_by'] = Auth::id();
            $party->update($data);
        });

        return $party->refresh();
    }

    /**
     * Delete a party (with validation)
     */
    public function deleteParty(Party $party): bool
    {
        // Check if party has any transactions
        if ($party->salesInvoices()->exists() || $party->receiptVouchers()->exists()) {
            throw new \Exception(__('finance::party.cannot_delete_has_transactions'));
        }

        return $party->delete();
    }

    /**
     * Toggle party active status
     */
    public function toggleStatus(Party $party): Party
    {
        $party->update([
            'is_active' => !$party->is_active,
            'edited_by' => Auth::id(),
        ]);

        return $party->refresh();
    }

    /**
     * Get party statistics
     */
    public function getStatistics(): array
    {
        $allParties = Party::all();
        
        return [
            'total_parties' => $allParties->count(),
            'total_customers' => $allParties->filter(fn($p) => $p->is_customer)->count(),
            'total_vendors' => $allParties->filter(fn($p) => $p->is_vendor)->count(),
            'active_parties' => Party::active()->count(),
            'total_receivables' => $allParties->sum('customer_balance'),
            'total_payables' => $allParties->sum('vendor_balance'),
        ];
    }

    /**
     * Get party financial summary
     */
    public function getPartySummary(Party $party): array
    {
        return [
            'total_sales' => $party->total_sales,
            'total_payments_received' => $party->total_payments_received,
            'customer_balance' => $party->customer_balance,
            'vendor_balance' => $party->vendor_balance,
            'unpaid_invoices_count' => $party->unpaid_invoices_count,
            'paid_invoices_count' => $party->paid_invoices_count,
        ];
    }
}