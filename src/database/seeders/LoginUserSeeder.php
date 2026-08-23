<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Creates (or updates the password of) the single login account used to
 * gate SBI holdings / theme management. Credentials come from .env only —
 * never hardcode them here (see the earlier hardcoded-API-key incident in
 * this project's history).
 */
class LoginUserSeeder extends Seeder
{
    public function run(): void
    {
        $email = env('LOGIN_EMAIL');
        $password = env('LOGIN_PASSWORD');

        if (! $email || ! $password) {
            $this->command?->warn('LOGIN_EMAIL / LOGIN_PASSWORD が .env に設定されていないため、ログインアカウントの作成をスキップしました。');

            return;
        }

        User::updateOrCreate(
            ['email' => $email],
            ['name' => 'Owner', 'password' => Hash::make($password)]
        );

        $this->command?->info("ログインアカウントを作成/更新しました: {$email}");
    }
}
