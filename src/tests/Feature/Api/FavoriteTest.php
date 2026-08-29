<?php

namespace Tests\Feature\Api;

use App\Models\Favorite;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class FavoriteTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        DB::table('markets')->insert(['market' => 'プライム']);
        DB::table('stocks')->insert(['code' => '7203', 'stockName' => 'トヨタ自動車', 'market_id' => 1]);
        DB::table('stocks')->insert(['code' => '6758', 'stockName' => 'ソニーグループ', 'market_id' => 1]);
    }

    public function test_guest_cannot_view_favorites(): void
    {
        $response = $this->getJson('/api/favorites');

        $response->assertStatus(401);
    }

    public function test_user_can_add_a_favorite(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/favorites', ['code' => '7203']);

        $response->assertCreated();
        $this->assertDatabaseHas('favorites', ['user_id' => $user->id, 'code' => '7203']);
    }

    public function test_adding_the_same_favorite_twice_does_not_duplicate(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->postJson('/api/favorites', ['code' => '7203']);
        $this->actingAs($user)->postJson('/api/favorites', ['code' => '7203']);

        $this->assertDatabaseCount('favorites', 1);
    }

    public function test_code_must_reference_an_existing_stock(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/favorites', ['code' => '9999']);

        $response->assertJsonValidationErrors('code');
    }

    public function test_index_lists_only_the_current_users_favorites(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();

        Favorite::create(['user_id' => $user->id, 'code' => '7203']);
        Favorite::create(['user_id' => $other->id, 'code' => '6758']);

        $response = $this->actingAs($user)->getJson('/api/favorites');

        $response->assertOk();
        $codes = collect($response->json('stocks'))->pluck('code');
        $this->assertTrue($codes->contains('7203'));
        $this->assertFalse($codes->contains('6758'));
    }

    public function test_user_can_remove_a_favorite(): void
    {
        $user = User::factory()->create();
        Favorite::create(['user_id' => $user->id, 'code' => '7203']);

        $response = $this->actingAs($user)->deleteJson('/api/favorites/7203');

        $response->assertNoContent();
        $this->assertDatabaseMissing('favorites', ['user_id' => $user->id, 'code' => '7203']);
    }

    public function test_removing_another_users_favorite_does_not_affect_it(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        Favorite::create(['user_id' => $other->id, 'code' => '7203']);

        $this->actingAs($user)->deleteJson('/api/favorites/7203');

        $this->assertDatabaseHas('favorites', ['user_id' => $other->id, 'code' => '7203']);
    }

    public function test_stocks_index_reports_favorite_codes_for_logged_in_user(): void
    {
        $user = User::factory()->create();
        Favorite::create(['user_id' => $user->id, 'code' => '7203']);

        $response = $this->actingAs($user)->getJson('/api/stocks');

        $response->assertOk();
        $response->assertJsonPath('favoriteCodes', ['7203']);
    }

    public function test_stocks_index_reports_no_favorites_for_guest(): void
    {
        $response = $this->getJson('/api/stocks');

        $response->assertOk();
        $response->assertJsonPath('favoriteCodes', []);
    }

    public function test_stock_show_reports_is_favorited(): void
    {
        Http::fake();

        $user = User::factory()->create();
        Favorite::create(['user_id' => $user->id, 'code' => '7203']);

        $response = $this->actingAs($user)->getJson('/api/stocks/7203');

        $response->assertOk();
        $response->assertJsonPath('isFavorited', true);
    }
}
