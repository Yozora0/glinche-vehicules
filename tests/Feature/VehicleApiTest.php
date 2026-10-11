<?php

namespace Tests\Feature;

use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class VehicleApiTest extends TestCase
{
    use RefreshDatabase;

    private function fakeGlincheApi(): void
    {
        Http::fake([
            'glinche.test/api/partners/login' => Http::response(['token' => 'fake-token']),
            'glinche.test/api/partners/vehicles' => Http::response($this->fixture('vehicles')),
        ]);
    }

    public function test_first_request_syncs_from_the_api_and_lists_vehicles(): void
    {
        $this->fakeGlincheApi();

        $response = $this->getJson('/api/vehicles');

        $response->assertOk()
            ->assertJsonCount(3, 'data')
            ->assertJsonPath('meta.count', 3)
            ->assertJsonStructure(['data' => [['id', 'brand', 'model', 'version', 'mileage', 'energy_label', 'price', 'picture']]]);

        Http::assertSent(fn (Request $r) => $r->url() === 'https://glinche.test/api/partners/vehicles'
            && $r->hasHeader('Authorization', 'Bearer fake-token'));
    }

    public function test_vehicles_can_be_filtered_by_brand(): void
    {
        $this->fakeGlincheApi();

        $this->getJson('/api/vehicles?brand=renault')
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.brand', 'Renault');

        $this->getJson('/api/vehicles?brand=Peugeot')
            ->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_brands_are_built_from_available_vehicles(): void
    {
        $this->fakeGlincheApi();

        $this->getJson('/api/brands')
            ->assertOk()
            ->assertExactJson(['data' => [
                ['name' => 'Peugeot', 'count' => 1],
                ['name' => 'Renault', 'count' => 2],
            ]]);
    }

    public function test_fresh_data_does_not_call_the_api_again(): void
    {
        $this->fakeGlincheApi();

        $this->getJson('/api/vehicles')->assertOk();
        $this->getJson('/api/vehicles')->assertOk();
        $this->getJson('/api/brands')->assertOk();

        Http::assertSentCount(2);
    }

    public function test_expired_token_triggers_a_single_relogin(): void
    {
        Http::fake([
            'glinche.test/api/partners/login' => Http::sequence()
                ->push(['token' => 'old-token'])
                ->push(['token' => 'new-token']),
            'glinche.test/api/partners/vehicles' => Http::sequence()
                ->push(['message' => 'Unauthenticated.'], 401)
                ->push($this->fixture('vehicles')),
        ]);

        $this->getJson('/api/vehicles')->assertOk()->assertJsonCount(3, 'data');
    }

    public function test_api_failure_with_empty_database_returns_502(): void
    {
        Http::fake([
            'glinche.test/api/partners/login' => Http::response(['message' => 'Invalid credentials'], 401),
        ]);

        $this->getJson('/api/vehicles')
            ->assertStatus(502)
            ->assertJsonStructure(['message']);
    }

    public function test_vehicles_no_longer_in_the_feed_are_removed(): void
    {
        Vehicle::factory()->create(['reference' => 'old-ref', 'brand' => 'Fiat']);
        $this->fakeGlincheApi();

        $this->postJson('/api/vehicles/sync')->assertOk()->assertJsonPath('stats.removed', 1);

        $this->assertDatabaseMissing('vehicles', ['reference' => 'old-ref']);
        $this->assertDatabaseCount('vehicles', 3);
    }

    public function test_vehicle_detail_returns_gallery_and_sections(): void
    {
        $this->fakeGlincheApi();
        $this->postJson('/api/vehicles/sync')->assertOk();

        $vehicle = Vehicle::where('reference', '284365')->firstOrFail();

        $this->getJson("/api/vehicles/{$vehicle->id}")
            ->assertOk()
            ->assertJsonPath('brand', 'Renault')
            ->assertJsonPath('pictures.0', 'https://api.glinche-automobiles.com/storage/vehicles/pictures/8481-1.jpg')
            ->assertJsonCount(2, 'pictures')
            ->assertJsonPath('sections.0.title', 'Informations générales')
            ->assertJsonPath('options.0.name', 'Peinture métallisée')
            ->assertJsonMissingPath('raw');
    }

    public function test_list_does_not_expose_raw_advert(): void
    {
        $this->fakeGlincheApi();

        $this->getJson('/api/vehicles')->assertOk()->assertJsonMissingPath('data.0.raw');
    }

    public function test_unknown_vehicle_returns_404(): void
    {
        $this->getJson('/api/vehicles/999')->assertNotFound();
    }

    public function test_brand_filter_is_validated(): void
    {
        $this->getJson('/api/vehicles?brand='.str_repeat('a', 150))
            ->assertStatus(422);
    }
}
