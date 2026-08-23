<?php

namespace App\Console\Commands;

use App\Models\SbiHolding;
use App\Models\Theme;
use App\Models\User;
use Illuminate\Console\Command;

/**
 * One-time migration helper: before self-registration existed, SBI
 * holdings and manually-created themes had no owner. This assigns any
 * still-unowned rows to the given user so they don't stay invisible
 * forever behind the new per-user scoping.
 */
class ClaimLegacyData extends Command
{
    protected $signature = 'app:claim-legacy-data {email}';

    protected $description = '所有者未設定のSBI保有株・手動作成テーマを指定ユーザーに割り当てます';

    public function handle(): int
    {
        $user = User::where('email', $this->argument('email'))->first();

        if (! $user) {
            $this->error("ユーザーが見つかりません: {$this->argument('email')}");

            return self::FAILURE;
        }

        $holdings = SbiHolding::whereNull('user_id')->update(['user_id' => $user->id]);
        $themes = Theme::whereNull('user_id')->where('source', 'manual')->update(['user_id' => $user->id]);

        $this->info("SBI保有株 {$holdings} 件、手動作成テーマ {$themes} 件を {$user->email} に割り当てました。");

        return self::SUCCESS;
    }
}
