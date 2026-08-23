<?php

namespace Tests\Feature;

use App\Models\Theme;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ThemeCrudTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Http::fake(['*' => Http::response(['price' => '30000'])]);

        DB::table('markets')->insert(['market' => 'プライム']);
        DB::table('stocks')->insert(['code' => 7203, 'stockName' => 'トヨタ自動車', 'market_id' => 1]);
    }

    public function test_dashboard_lists_themes(): void
    {
        Theme::create(['name' => '自動車']);

        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('自動車');
    }

    public function test_guest_cannot_create_a_theme(): void
    {
        $response = $this->post('/themes', ['name' => '半導体', 'description' => 'テスト']);

        $response->assertRedirect(route('login'));
        $this->assertDatabaseMissing('themes', ['name' => '半導体']);
    }

    public function test_logged_in_user_can_create_a_theme(): void
    {
        $response = $this->actingAs(User::factory()->create())
            ->post('/themes', ['name' => '半導体', 'description' => 'テスト']);

        $response->assertRedirect(route('themes.dashboard'));
        $this->assertDatabaseHas('themes', ['name' => '半導体']);
    }

    public function test_theme_name_must_be_unique(): void
    {
        Theme::create(['name' => '半導体']);

        $response = $this->actingAs(User::factory()->create())
            ->post('/themes', ['name' => '半導体']);

        $response->assertSessionHasErrors('name');
    }

    public function test_stock_can_be_assigned_to_themes(): void
    {
        $theme = Theme::create(['name' => '自動車']);

        $response = $this->actingAs(User::factory()->create())
            ->put('/stocks/7203/themes', ['theme_ids' => [$theme->id]]);

        $response->assertRedirect(route('stocks.show', 7203));
        $this->assertDatabaseHas('stock_theme', ['stock_code' => 7203, 'theme_id' => $theme->id]);
    }

    public function test_theme_can_be_deleted(): void
    {
        $theme = Theme::create(['name' => '自動車']);

        $response = $this->actingAs(User::factory()->create())
            ->delete("/themes/{$theme->id}");

        $response->assertRedirect(route('themes.dashboard'));
        $this->assertDatabaseMissing('themes', ['id' => $theme->id]);
    }
}
