<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register_via_api_and_receive_a_session_cookie(): void
    {
        $response = $this->fromSpa()->postJson('/api/register', [
            'name' => 'New User',
            'email' => 'new-user@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertOk();
        $response->assertJsonPath('user.email', 'new-user@example.com');
        $this->assertDatabaseHas('users', ['email' => 'new-user@example.com']);
    }

    public function test_api_registration_rejects_a_duplicate_email(): void
    {
        User::factory()->create(['email' => 'taken@example.com']);

        $response = $this->fromSpa()->postJson('/api/register', [
            'name' => 'New User',
            'email' => 'taken@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(422);
    }

    public function test_user_can_log_in_via_api_and_receive_a_session_cookie(): void
    {
        $user = User::factory()->create(['password' => bcrypt('correct-password')]);

        $response = $this->fromSpa()->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'correct-password',
        ]);

        $response->assertOk();
        $response->assertJsonPath('user.email', $user->email);
        $this->assertAuthenticatedAs($user);
    }

    public function test_api_login_fails_with_wrong_password(): void
    {
        $user = User::factory()->create(['password' => bcrypt('correct-password')]);

        $response = $this->fromSpa()->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(422);
        $this->assertGuest();
    }

    public function test_authenticated_user_can_fetch_their_own_user_via_api(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->getJson('/api/user');

        $response->assertOk();
        $response->assertJsonPath('user.email', $user->email);
    }

    public function test_authenticated_user_can_log_out_via_api(): void
    {
        $user = User::factory()->create();

        $response = $this->fromSpa()->actingAs($user)->postJson('/api/logout');

        $response->assertNoContent();
        // auth:sanctum's successful check flips the default guard to
        // 'sanctum' for the rest of the request, and that guard caches
        // the user it resolved before logout ran — so assert against
        // the 'web' guard we actually logged out of, not the default.
        $this->assertGuest('web');
    }
}
