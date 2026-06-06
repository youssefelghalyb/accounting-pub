<?php

namespace Modules\Product\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Modules\Product\Models\Author;
use Modules\Product\Models\ContractTransaction;

class AuthorService
{
    /**
     * Create a new author, optionally uploading their ID image.
     */
    public function createAuthor(array $data, ?UploadedFile $idImage = null): Author
    {
        if ($idImage) {
            $data['id_image'] = $idImage->store('authors/ids', 'public');
        }

        return Author::create($data);
    }

    /**
     * Update an existing author.
     */
    public function updateAuthor(Author $author, array $data, ?UploadedFile $idImage = null): Author
    {
        if ($idImage) {
            $this->deleteFile($author->id_image);
            $data['id_image'] = $idImage->store('authors/ids', 'public');
        }

        $author->update($data);

        return $author->fresh();
    }

    /**
     * Delete an author — guards against authors that still have contracts.
     *
     * @throws \RuntimeException
     */
    public function deleteAuthor(Author $author): void
    {
        if ($author->contracts()->count() > 0) {
            throw new \RuntimeException(__('product::author.cannot_delete_has_contracts'));
        }

        $this->deleteFile($author->id_image);
        $author->delete();
    }

    /**
     * Full financial summary for an author's show page.
     */
    public function getAuthorStats(Author $author): array
    {
        $contracts = $author->contracts()->with('transactions')->get();

        $totalContractValue = $contracts->sum('contract_price');
        $totalPaid          = $contracts->sum('total_paid');

        // Gift copies: line items on author's own books with 100% discount
        $productIds = $contracts->pluck('book.product.id')->filter()->unique();
        $giftCopiesCount = 0;
        if ($productIds->isNotEmpty()) {
            $giftCopiesCount = \Modules\Finance\Models\SalesInvoiceItem::whereIn('product_id', $productIds)
                ->whereRaw('discount_amount >= (unit_price * quantity)')
                ->sum('quantity');
        }

        // Invoice count if author is also a client
        $invoiceCount = 0;
        if ($author->party_id) {
            $invoiceCount = \Modules\Finance\Models\SalesInvoice::where('party_id', $author->party_id)->count();
        }

        return [
            'total_books'          => $contracts->whereNotNull('book_id')->count(),
            'total_contracts'      => $contracts->count(),
            'total_contract_value' => $totalContractValue,
            'total_paid'           => $totalPaid,
            'outstanding_balance'  => max(0, $totalContractValue - $totalPaid),
            'gift_copies_count'    => $giftCopiesCount,
            'invoice_count'        => $invoiceCount,
        ];
    }

    /**
     * All payment transactions across every contract this author appears on.
     */
    public function getAuthorTransactions(Author $author)
    {
        return ContractTransaction::whereHas('contract', function ($query) use ($author) {
            $query->whereHas('authors', function ($q) use ($author) {
                $q->where('authors.id', $author->id);
            });
        })->with('contract.book.product')->latest('payment_date')->get();
    }

    private function deleteFile(?string $path): void
    {
        if ($path) {
            Storage::disk('public')->delete($path);
        }
    }
}
