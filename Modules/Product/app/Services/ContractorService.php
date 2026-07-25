<?php

namespace Modules\Product\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Modules\Finance\Models\Party;
use Modules\Finance\Services\PartyService;
use Modules\Product\Models\Contractor;
use Modules\Product\Models\ContractorBook;

class ContractorService
{
    public function __construct(private PartyService $partyService) {}

    public function createContractor(array $data, ?UploadedFile $nationalIdFile = null): Contractor
    {
        if ($nationalIdFile) {
            $data['national_id_file'] = $nationalIdFile->store('contractors/ids', 'public');
        }

        return Contractor::create($data);
    }

    public function updateContractor(Contractor $contractor, array $data, ?UploadedFile $nationalIdFile = null): Contractor
    {
        if ($nationalIdFile) {
            $this->deleteFile($contractor->national_id_file);
            $data['national_id_file'] = $nationalIdFile->store('contractors/ids', 'public');
        }

        $contractor->update($data);

        return $contractor->fresh();
    }

    /**
     * Delete a contractor — guards against contractors that still have contractor_books.
     *
     * @throws \RuntimeException
     */
    public function deleteContractor(Contractor $contractor): void
    {
        if ($contractor->contractorBooks()->count() > 0) {
            throw new \RuntimeException(__('product::contractor.cannot_delete_has_books'));
        }

        $this->deleteFile($contractor->national_id_file);
        $contractor->delete();
    }

    /**
     * Create or update the ContractorBook contract for a book.
     */
    public function assignBook(Contractor $contractor, array $data, ?UploadedFile $contractFile = null): ContractorBook
    {
        return DB::transaction(function () use ($contractor, $data, $contractFile) {
            $contractorBook = ContractorBook::firstOrNew(['book_id' => $data['book_id']]);

            if ($contractFile) {
                $this->deleteFile($contractorBook->contract_file);
                $data['contract_file'] = $contractFile->store('contractor_books', 'public');
            }

            $data['contractor_id'] = $contractor->id;
            $contractorBook->fill($data);
            $contractorBook->save();

            return $contractorBook->fresh();
        });
    }

    public function unassignBook(ContractorBook $contractorBook): void
    {
        if ($contractorBook->bookSales()->count() > 0) {
            throw new \RuntimeException(__('product::contractor.cannot_unassign_has_sales'));
        }

        $this->deleteFile($contractorBook->contract_file);
        $contractorBook->delete();
    }

    /**
     * Ensure a contractor has a Party, auto-creating one if they don't — reused by both the
     * gift flow (ContractorGiftService) and the transaction flow (ContractTransactionService)
     * so this bridge logic exists in exactly one place.
     */
    public function ensureParty(Contractor $contractor): Party
    {
        if ($contractor->party_id) {
            return Party::findOrFail($contractor->party_id);
        }

        return DB::transaction(function () use ($contractor) {
            $party = $this->partyService->createParty([
                'name' => $contractor->name,
                'type' => 'individual',
                'email' => $contractor->email,
                'phone' => $contractor->phone,
                'address' => $contractor->address,
            ]);

            $contractor->update(['party_id' => $party->id]);

            return $party;
        });
    }

    /**
     * Financial summary for a contractor's show page.
     */
    public function getContractorStats(Contractor $contractor): array
    {
        $royaltyAccrued = $contractor->bookSales()->where('is_gift', false)->sum('contractor_profit');
        $royaltyPaid = $contractor->contractTransactions()->ofType('royalty_payment')->outgoing()->sum('amount');
        $giftCopiesQuantity = $contractor->bookSales()->where('is_gift', true)->sum('quantity');

        return [
            'total_books' => $contractor->contractorBooks()->count(),
            'total_royalty_accrued' => (float) $royaltyAccrued,
            'total_royalty_paid' => (float) $royaltyPaid,
            'outstanding_royalty' => (float) $royaltyAccrued - (float) $royaltyPaid,
            'gift_copies_count' => (int) $giftCopiesQuantity,
            'invoice_count' => $contractor->party_id
                ? \Modules\Finance\Models\SalesInvoice::where('party_id', $contractor->party_id)->count()
                : 0,
        ];
    }

    private function deleteFile(?string $path): void
    {
        if ($path) {
            Storage::disk('public')->delete($path);
        }
    }
}
