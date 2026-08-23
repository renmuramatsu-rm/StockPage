<?php

namespace Tests\Feature\Api;

use App\Models\SbiHolding;
use App\Models\StockScore;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SbiHoldingTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        DB::table('markets')->insert(['market' => 'プライム']);
        DB::table('stocks')->insert(['code' => '7203', 'stockName' => 'トヨタ自動車', 'market_id' => 1]);

        $this->user = User::factory()->create();
    }

    private function asLoggedIn()
    {
        return $this->actingAs($this->user);
    }

    public function test_guest_cannot_view_holdings(): void
    {
        $response = $this->getJson('/api/sbi-holdings');

        $response->assertStatus(401);
    }

    public function test_guest_cannot_create_a_holding(): void
    {
        $response = $this->postJson('/api/sbi-holdings', [
            'code' => '7203',
            'shares' => 100,
            'average_acquisition_price' => 2000,
        ]);

        $response->assertStatus(401);
        $this->assertDatabaseMissing('sbi_holdings', ['code' => '7203']);
    }

    public function test_holding_can_be_created(): void
    {
        $response = $this->asLoggedIn()->postJson('/api/sbi-holdings', [
            'code' => '7203',
            'shares' => 100,
            'average_acquisition_price' => 2000,
        ]);

        $response->assertCreated();
        $this->assertDatabaseHas('sbi_holdings', ['code' => '7203', 'shares' => 100, 'user_id' => $this->user->id]);
    }

    public function test_code_must_reference_an_existing_stock(): void
    {
        $response = $this->asLoggedIn()->postJson('/api/sbi-holdings', [
            'code' => '9999',
            'shares' => 100,
            'average_acquisition_price' => 2000,
        ]);

        $response->assertJsonValidationErrors('code');
    }

    public function test_index_reads_price_from_persisted_score_without_any_live_api_call(): void
    {
        SbiHolding::create(['user_id' => $this->user->id, 'code' => '7203', 'shares' => 100, 'average_acquisition_price' => 2000]);
        StockScore::create([
            'code' => '7203',
            'current_price' => 2500,
            'price_date' => '2026-08-20',
            'price_change' => 50,
            'price_change_percent' => 2.04,
        ]);

        Http::fake();

        $response = $this->asLoggedIn()->getJson('/api/sbi-holdings');

        $response->assertOk();
        $response->assertJsonPath('holdings.0.current_price', 2500);
        Http::assertNothingSent();
    }

    public function test_index_does_not_show_another_users_holdings(): void
    {
        SbiHolding::create(['user_id' => User::factory()->create()->id, 'code' => '7203', 'shares' => 100, 'average_acquisition_price' => 2000]);

        $response = $this->asLoggedIn()->getJson('/api/sbi-holdings');

        $response->assertOk();
        $this->assertCount(0, $response->json('holdings'));
    }

    public function test_only_one_holding_row_allowed_per_stock_per_user(): void
    {
        SbiHolding::create(['user_id' => $this->user->id, 'code' => '7203', 'shares' => 100, 'average_acquisition_price' => 2000]);

        $this->expectException(\Illuminate\Database\QueryException::class);
        SbiHolding::create(['user_id' => $this->user->id, 'code' => '7203', 'shares' => 50, 'average_acquisition_price' => 2100]);
    }

    public function test_two_users_can_each_hold_the_same_stock(): void
    {
        SbiHolding::create(['user_id' => User::factory()->create()->id, 'code' => '7203', 'shares' => 100, 'average_acquisition_price' => 2000]);

        $response = $this->asLoggedIn()->postJson('/api/sbi-holdings', [
            'code' => '7203',
            'shares' => 50,
            'average_acquisition_price' => 2100,
        ]);

        $response->assertCreated();
        $this->assertDatabaseHas('sbi_holdings', ['code' => '7203', 'shares' => 50, 'user_id' => $this->user->id]);
    }

    public function test_creating_a_duplicate_holding_shows_a_friendly_validation_error(): void
    {
        SbiHolding::create(['user_id' => $this->user->id, 'code' => '7203', 'shares' => 100, 'average_acquisition_price' => 2000]);

        $response = $this->asLoggedIn()->postJson('/api/sbi-holdings', [
            'code' => '7203',
            'shares' => 50,
            'average_acquisition_price' => 2100,
        ]);

        $response->assertJsonValidationErrors('code');
        $this->assertDatabaseCount('sbi_holdings', 1);
    }

    public function test_updating_a_holding_does_not_trigger_duplicate_validation_against_itself(): void
    {
        $holding = SbiHolding::create(['user_id' => $this->user->id, 'code' => '7203', 'shares' => 100, 'average_acquisition_price' => 2000]);

        $response = $this->asLoggedIn()->putJson("/api/sbi-holdings/{$holding->id}", [
            'code' => '7203',
            'shares' => 150,
            'average_acquisition_price' => 2050,
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('sbi_holdings', ['code' => '7203', 'shares' => 150]);
    }

    public function test_user_cannot_view_another_users_holding(): void
    {
        $holding = SbiHolding::create(['user_id' => User::factory()->create()->id, 'code' => '7203', 'shares' => 100, 'average_acquisition_price' => 2000]);

        $response = $this->asLoggedIn()->getJson("/api/sbi-holdings/{$holding->id}");

        $response->assertForbidden();
    }

    public function test_user_cannot_update_another_users_holding(): void
    {
        $holding = SbiHolding::create(['user_id' => User::factory()->create()->id, 'code' => '7203', 'shares' => 100, 'average_acquisition_price' => 2000]);

        $response = $this->asLoggedIn()->putJson("/api/sbi-holdings/{$holding->id}", [
            'code' => '7203',
            'shares' => 150,
            'average_acquisition_price' => 2050,
        ]);

        $response->assertForbidden();
    }

    public function test_user_cannot_delete_another_users_holding(): void
    {
        $holding = SbiHolding::create(['user_id' => User::factory()->create()->id, 'code' => '7203', 'shares' => 100, 'average_acquisition_price' => 2000]);

        $response = $this->asLoggedIn()->deleteJson("/api/sbi-holdings/{$holding->id}");

        $response->assertForbidden();
        $this->assertDatabaseHas('sbi_holdings', ['id' => $holding->id]);
    }
}
