<?php

namespace Modules\Product\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Modules\Product\Models\Contract;

class ContractService
{
    /**
     * Create a new contract and attach its authors.
     *
     * @param  array         $data           Validated contract fields
     * @param  array         $authorIds      All author IDs to attach
     * @param  int           $representativeId  The author who is the representative
     * @param  UploadedFile|null $file
     */
    public function createContract(
        array $data,
        array $authorIds,
        int $representativeId,
        ?UploadedFile $file = null
    ): Contract {
        return DB::transaction(function () use ($data, $authorIds, $representativeId, $file) {

            if ($file) {
                $data['contract_file'] = $file->store('contracts', 'public');
            }

            $contract = Contract::create($data);

            $this->syncAuthors($contract, $authorIds, $representativeId);

            return $contract;
        });
    }

    /**
     * Update an existing contract and re-sync its authors.
     */
    public function updateContract(
        Contract $contract,
        array $data,
        array $authorIds,
        int $representativeId,
        ?UploadedFile $file = null
    ): Contract {
        return DB::transaction(function () use ($contract, $data, $authorIds, $representativeId, $file) {

            if ($file) {
                $this->deleteFile($contract->contract_file);
                $data['contract_file'] = $file->store('contracts', 'public');
            }

            $contract->update($data);

            $this->syncAuthors($contract, $authorIds, $representativeId);

            return $contract->fresh();
        });
    }

    /**
     * Delete a contract — guards against contracts that have transactions.
     *
     * @throws \RuntimeException
     */
    public function deleteContract(Contract $contract): void
    {
        if ($contract->transactions()->count() > 0) {
            throw new \RuntimeException(__('product::contract.cannot_delete_has_transactions'));
        }

        DB::transaction(function () use ($contract) {
            $this->deleteFile($contract->contract_file);
            $contract->authors()->detach();
            $contract->delete();
        });
    }

    /**
     * Sync the authors pivot for a contract.
     * Builds the sync array with is_representative flag per author.
     */
    private function syncAuthors(Contract $contract, array $authorIds, int $representativeId): void
    {
        // Ensure representative is always in the list
        if (! in_array($representativeId, $authorIds)) {
            $authorIds[] = $representativeId;
        }

        $syncData = [];
        foreach ($authorIds as $authorId) {
            $syncData[$authorId] = [
                'is_representative' => ($authorId == $representativeId),
            ];
        }

        $contract->authors()->sync($syncData);
    }

    /**
     * Safely delete a stored file.
     */
    private function deleteFile(?string $path): void
    {
        if ($path) {
            Storage::disk('public')->delete($path);
        }
    }
}