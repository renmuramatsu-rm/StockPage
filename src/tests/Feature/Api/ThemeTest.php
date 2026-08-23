<?php

namespace Tests\Feature\Api;

use App\Models\Theme;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ThemeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Http::fake(['*' => Http::response(['price' => '30000'])]);

        DB::table('markets')->insert(['market' => 'プライム']);
        DB::table('stocks')->insert(['code' => 7203, 'stockName' => 'トヨタ自動車', 'market_id' => 1]);
    }

    public function test_guest_cannot_view_the_dashboard(): void
    {
        $response = $this->getJson('/api/dashboard');

        $response->assertStatus(401);
    }

    public function test_dashboard_lists_own_themes_and_system_themes(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();

        Theme::create(['user_id' => $user->id, 'name' => '自分のテーマ']);
        Theme::create(['user_id' => $other->id, 'name' => '他人のテーマ']);
        Theme::create(['user_id' => null, 'name' => '自動車', 'source' => 'jquants_17']);

        $response = $this->actingAs($user)->getJson('/api/dashboard');

        $response->assertOk();
        $names = collect($response->json('themes'))->pluck('name');
        $this->assertTrue($names->contains('自分のテーマ'));
        $this->assertTrue($names->contains('自動車'));
        $this->assertFalse($names->contains('他人のテーマ'));
    }

    public function test_guest_cannot_create_a_theme(): void
    {
        $response = $this->postJson('/api/themes', ['name' => '半導体', 'description' => 'テスト']);

        $response->assertStatus(401);
        $this->assertDatabaseMissing('themes', ['name' => '半導体']);
    }

    public function test_logged_in_user_can_create_a_theme(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/themes', ['name' => '半導体', 'description' => 'テスト']);

        $response->assertCreated();
        $this->assertDatabaseHas('themes', ['name' => '半導体', 'user_id' => $user->id]);
    }

    public function test_theme_name_must_be_unique_per_user(): void
    {
        $user = User::factory()->create();
        Theme::create(['user_id' => $user->id, 'name' => '半導体']);

        $response = $this->actingAs($user)->postJson('/api/themes', ['name' => '半導体']);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('name');
    }

    public function test_two_users_can_use_the_same_theme_name(): void
    {
        Theme::create(['user_id' => User::factory()->create()->id, 'name' => '半導体']);

        $response = $this->actingAs(User::factory()->create())->postJson('/api/themes', ['name' => '半導体']);

        $response->assertCreated();
    }

    public function test_stock_can_be_assigned_to_own_theme(): void
    {
        $user = User::factory()->create();
        $theme = Theme::create(['user_id' => $user->id, 'name' => '自動車']);

        $response = $this->actingAs($user)->putJson('/api/stocks/7203/themes', ['theme_ids' => [$theme->id]]);

        $response->assertOk();
        $this->assertDatabaseHas('stock_theme', ['stock_code' => 7203, 'theme_id' => $theme->id]);
    }

    public function test_assigning_themes_does_not_touch_other_users_assignments(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $ownTheme = Theme::create(['user_id' => $user->id, 'name' => '自分のテーマ']);
        $otherTheme = Theme::create(['user_id' => $other->id, 'name' => '他人のテーマ']);

        DB::table('stock_theme')->insert(['stock_code' => 7203, 'theme_id' => $otherTheme->id]);

        $this->actingAs($user)->putJson('/api/stocks/7203/themes', ['theme_ids' => [$ownTheme->id, $otherTheme->id]]);

        $this->assertDatabaseHas('stock_theme', ['stock_code' => 7203, 'theme_id' => $ownTheme->id]);
        $this->assertDatabaseHas('stock_theme', ['stock_code' => 7203, 'theme_id' => $otherTheme->id]);
    }

    public function test_theme_can_be_deleted_by_its_owner(): void
    {
        $user = User::factory()->create();
        $theme = Theme::create(['user_id' => $user->id, 'name' => '自動車']);

        $response = $this->actingAs($user)->deleteJson("/api/themes/{$theme->id}");

        $response->assertNoContent();
        $this->assertDatabaseMissing('themes', ['id' => $theme->id]);
    }

    public function test_user_cannot_delete_another_users_theme(): void
    {
        $theme = Theme::create(['user_id' => User::factory()->create()->id, 'name' => '自動車']);

        $response = $this->actingAs(User::factory()->create())->deleteJson("/api/themes/{$theme->id}");

        $response->assertForbidden();
        $this->assertDatabaseHas('themes', ['id' => $theme->id]);
    }

    public function test_user_cannot_delete_a_system_theme(): void
    {
        $theme = Theme::create(['user_id' => null, 'name' => '自動車', 'source' => 'jquants_17']);

        $response = $this->actingAs(User::factory()->create())->deleteJson("/api/themes/{$theme->id}");

        $response->assertForbidden();
        $this->assertDatabaseHas('themes', ['id' => $theme->id]);
    }
}
