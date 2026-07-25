<?php

namespace Modules\Product\Imports;

use Modules\Product\Services\BooksImportService;

/**
 * Thin wrapper around BooksImportService for the web upload endpoint
 * (BookController::import()). See BooksImportService for the actual logic.
 */
class BooksImport
{
    public function import($uploadedFile): array
    {
        return $this->importFromPath($uploadedFile->getRealPath());
    }

    public function importFromPath(string $path): array
    {
        return (new BooksImportService())->importFromPath($path);
    }
}
