<?php

namespace Tests\Feature;

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

    public function test_allowed_google_account_can_log_in_and_creates_a_user(): void
    {
        config(['services.login.allowed_email' => 'owner@example.com']);
        $this->fakeGoogleUser('google-123', 'Owner', 'owner@example.com');

        $response = $this->get('/auth/google/callback');

        $response->assertRedirect(route('themes.dashboard'));
        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', ['email' => 'owner@example.com', 'google_id' => 'google-123']);
    }

    public function test_disallowed_google_account_is_rejected(): void
    {
        config(['services.login.allowed_email' => 'owner@example.com']);
        $this->fakeGoogleUser('google-999', 'Someone Else', 'stranger@example.com');

        $response = $this->get('/auth/google/callback');

        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
        $this->assertDatabaseMissing('users', ['email' => 'stranger@example.com']);
    }

    public function test_existing_user_is_reused_rather_than_duplicated(): void
    {
        config(['services.login.allowed_email' => 'owner@example.com']);

        $this->fakeGoogleUser('google-123', 'Owner', 'owner@example.com');
        $this->get('/auth/google/callback');

        $this->post('/logout');

        $this->fakeGoogleUser('google-123', 'Owner', 'owner@example.com');
        $this->get('/auth/google/callback');

        $this->assertDatabaseCount('users', 1);
    }
}
