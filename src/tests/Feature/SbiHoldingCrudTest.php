<?php

namespace Tests\Feature;

use App\Models\SbiHolding;
use App\Models\StockScore;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SbiHoldingCrudTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        DB::table('markets')->insert(['market' => 'プライム']);
        DB::table('stocks')->insert(['code' => '7203', 'stockName' => 'トヨタ自動車', 'market_id' => 1]);
    }

    private function asLoggedIn()
    {
        return $this->actingAs(User::factory()->create());
    }

    public function test_guest_is_redirected_to_login_when_viewing_holdings(): void
    {
        $response = $this->get('/sbi-holdings');

        $response->assertRedirect(route('login'));
    }

    public function test_guest_cannot_create_a_holding(): void
    {
        $response = $this->post('/sbi-holdings', [
            'code' => '7203',
            'shares' => 100,
            'average_acquisition_price' => 2000,
        ]);

        $response->assertRedirect(route('login'));
        $this->assertDatabaseMissing('sbi_holdings', ['code' => '7203']);
    }

    public function test_holding_can_be_created(): void
    {
        $response = $this->asLoggedIn()->post('/sbi-holdings', [
            'code' => '7203',
            'shares' => 100,
            'average_acquisition_price' => 2000,
        ]);

        $response->assertRedirect(route('sbi-holdings.index'));
        $this->assertDatabaseHas('sbi_holdings', ['code' => '7203', 'shares' => 100]);
    }

    public function test_code_must_reference_an_existing_stock(): void
    {
        $response = $this->asLoggedIn()->post('/sbi-holdings', [
            'code' => '9999',
            'shares' => 100,
            'average_acquisition_price' => 2000,
        ]);

        $response->assertSessionHasErrors('code');
    }

    public function test_index_reads_price_from_persisted_score_without_any_live_api_call(): void
    {
        SbiHolding::create(['code' => '7203', 'shares' => 100, 'average_acquisition_price' => 2000]);
        StockScore::create([
            'code' => '7203',
            'current_price' => 2500,
            'price_date' => '2026-08-20',
            'price_change' => 50,
            'price_change_percent' => 2.04,
        ]);

        Http::fake();

        $response = $this->asLoggedIn()->get('/sbi-holdings');

        $response->assertStatus(200);
        $response->assertSee('トヨタ自動車');
        $response->assertSee('2,500');
        Http::assertNothingSent();
    }

    public function test_index_shows_unsynced_message_when_no_score_exists(): void
    {
        SbiHolding::create(['code' => '7203', 'shares' => 100, 'average_acquisition_price' => 2000]);

        Http::fake();

        $response = $this->asLoggedIn()->get('/sbi-holdings');

        $response->assertStatus(200);
        $response->assertSee('未同期');
        Http::assertNothingSent();
    }

    public function test_only_one_holding_row_allowed_per_stock(): void
    {
        SbiHolding::create(['code' => '7203', 'shares' => 100, 'average_acquisition_price' => 2000]);

        $this->expectException(\Illuminate\Database\QueryException::class);
        SbiHolding::create(['code' => '7203', 'shares' => 50, 'average_acquisition_price' => 2100]);
    }

    public function test_creating_a_duplicate_holding_via_http_shows_a_friendly_validation_error(): void
    {
        SbiHolding::create(['code' => '7203', 'shares' => 100, 'average_acquisition_price' => 2000]);

        $response = $this->asLoggedIn()->post('/sbi-holdings', [
            'code' => '7203',
            'shares' => 50,
            'average_acquisition_price' => 2100,
        ]);

        $response->assertSessionHasErrors('code');
        $this->assertDatabaseCount('sbi_holdings', 1);
    }

    public function test_updating_a_holding_does_not_trigger_duplicate_validation_against_itself(): void
    {
        $holding = SbiHolding::create(['code' => '7203', 'shares' => 100, 'average_acquisition_price' => 2000]);

        $response = $this->asLoggedIn()->put("/sbi-holdings/{$holding->id}", [
            'code' => '7203',
            'shares' => 150,
            'average_acquisition_price' => 2050,
        ]);

        $response->assertRedirect(route('sbi-holdings.index'));
        $this->assertDatabaseHas('sbi_holdings', ['code' => '7203', 'shares' => 150]);
    }
}
