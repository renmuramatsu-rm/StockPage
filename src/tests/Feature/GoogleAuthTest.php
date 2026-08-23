<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Contracts\Provider;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use Mockery;
use Tests\TestCase;

class GoogleAuthTest extends TestCase
{
    use RefreshDatabase;

    private function fakeGoogleUser(string $id, string $name, string $email): void
    {
        $socialiteUser = (new SocialiteUser())->map(['id' => $id, 'name' => $name, 'email' => $email]);

        $provider = Mockery::mock(Provider::class);
        $provider->shouldReceive('user')->andReturn($socialiteUser);

        Socialite::shouldReceive('driver')->with('google')->andReturn($provider);
    }

    public function test_new_google_account_registers_and_logs_in(): void
    {
        $this->fakeGoogleUser('google-123', 'Someone New', 'someone@example.com');

        $response = $this->get('/auth/google/callback');

        $response->assertRedirect(config('app.frontend_url').'/login/callback');
        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', ['email' => 'someone@example.com', 'google_id' => 'google-123']);
    }

    public function test_google_auth_failure_redirects_to_frontend_login_with_error(): void
    {
        $provider = Mockery::mock(Provider::class);
        $provider->shouldReceive('user')->andThrow(new \Exception('oauth failed'));
        Socialite::shouldReceive('driver')->with('google')->andReturn($provider);

        $response = $this->get('/auth/google/callback');

        $response->assertRedirect(config('app.frontend_url').'/login?error=google_failed');
        $this->assertGuest();
    }

    public function test_existing_user_is_reused_rather_than_duplicated(): void
    {
        $this->fakeGoogleUser('google-123', 'Owner', 'owner@example.com');
        $this->get('/auth/google/callback');

        $this->postJson('/api/logout');

        $this->fakeGoogleUser('google-123', 'Owner', 'owner@example.com');
        $this->get('/auth/google/callback');

        $this->assertDatabaseCount('users', 1);
    }

    public function test_existing_email_password_user_is_matched_by_email_on_first_google_login(): void
    {
        $user = User::factory()->create(['email' => 'owner@example.com']);

        $this->fakeGoogleUser('google-999', 'Owner', 'owner@example.com');
        $this->get('/auth/google/callback');

        $this->assertAuthenticatedAs($user->fresh());
        $this->assertDatabaseCount('users', 1);
        $this->assertSame('google-999', $user->fresh()->google_id);
    }
}
