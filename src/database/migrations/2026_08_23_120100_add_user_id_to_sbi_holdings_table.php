<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * SBI holdings become per-user: the same stock code can now appear once
 * per owner rather than once globally. user_id stays nullable at the DB
 * level only to avoid orphaning the pre-existing rows created before
 * registration existed — the app always writes a user_id going forward,
 * and legacy null rows are invisible until claimed (see README note).
 *
 * The `up()` guards each step because a first attempt at this migration
 * partially applied on the dev database before failing (MySQL refuses
 * to drop the code-only unique index while the `code` foreign key still
 * depends on it) — a plain index on `code` gives that FK somewhere else
 * to live before the unique index underneath it is dropped.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('sbi_holdings', 'user_id')) {
            Schema::table('sbi_holdings', function (Blueprint $table) {
                $table->foreignId('user_id')->nullable()->after('id')->constrained()->cascadeOnDelete();
            });
        }

        if (! $this->indexExists('sbi_holdings', 'sbi_holdings_code_index')) {
            Schema::table('sbi_holdings', function (Blueprint $table) {
                $table->index('code');
            });
        }

        if ($this->indexExists('sbi_holdings', 'sbi_holdings_code_unique')) {
            Schema::table('sbi_holdings', function (Blueprint $table) {
                $table->dropUnique(['code']);
            });
        }

        if (! $this->indexExists('sbi_holdings', 'sbi_holdings_user_id_code_unique')) {
            Schema::table('sbi_holdings', function (Blueprint $table) {
                $table->unique(['user_id', 'code']);
            });
        }
    }

    public function down(): void
    {
        Schema::table('sbi_holdings', function (Blueprint $table) {
            $table->dropUnique(['user_id', 'code']);
            $table->dropIndex(['code']);
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });

        Schema::table('sbi_holdings', function (Blueprint $table) {
            $table->unique('code');
        });
    }

    private function indexExists(string $table, string $indexName): bool
    {
        return collect(Schema::getIndexes($table))->pluck('name')->contains($indexName);
    }
};
