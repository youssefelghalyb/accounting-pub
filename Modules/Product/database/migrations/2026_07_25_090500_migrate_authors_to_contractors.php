<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    /**
     * Data migration step 2 (docs/contractor-migration-plan.md §5): one `contractors` row per
     * existing `authors` row. Purely additive — `authors` is untouched here and only dropped by
     * a later, separate destructive migration. Idempotent via `legacy_author_id`.
     *
     * `authors.country_of_residence`, `bio`, and `occupation` have no destination column on the
     * new `contractors` table (per the business-specified field list) and are intentionally not
     * carried forward — flagged here rather than silently dropped.
     */
    public function up(): void
    {
        $alreadyMigrated = DB::table('contractors')
            ->whereNotNull('legacy_author_id')
            ->pluck('legacy_author_id')
            ->all();

        $authors = DB::table('authors')
            ->whereNotIn('id', $alreadyMigrated ?: [0])
            ->get();

        $now = now();
        $migrated = 0;

        foreach ($authors as $author) {
            try {
                DB::table('contractors')->insert([
                    'party_id' => $author->party_id,
                    'name' => $author->full_name,
                    'phone' => $author->phone_number,
                    'secondary_phone' => $author->whatsapp_number,
                    'email' => $author->email,
                    'secondary_email' => null,
                    'nationality' => $author->nationality,
                    'address' => null,
                    'national_id_file' => $author->id_image,
                    'legacy_author_id' => $author->id,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
                $migrated++;
            } catch (\Throwable $e) {
                Log::error("Author->Contractor migration: failed to migrate author #{$author->id} ({$author->full_name}): {$e->getMessage()}");
            }
        }

        Log::info('Author->Contractor migration: authors -> contractors complete.', ['migrated' => $migrated]);
    }

    public function down(): void
    {
        DB::table('contractors')->whereNotNull('legacy_author_id')->delete();
    }
};
