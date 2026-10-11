<?php

namespace Tests\Unit;

use App\Services\Glinche\AdvertMapper;
use PHPUnit\Framework\TestCase;

class AdvertMapperTest extends TestCase
{
    private function adverts(): array
    {
        return json_decode(file_get_contents(__DIR__.'/../Fixtures/vehicles.json'), true);
    }

    public function test_it_maps_the_main_fields(): void
    {
        $mapped = (new AdvertMapper)->map($this->adverts()[0]);

        $this->assertSame('284365', $mapped['reference']);
        $this->assertSame('Renault', $mapped['brand']);
        $this->assertSame('Austral', $mapped['model']);
        $this->assertSame('E-Tech full hybrid 200ch Techno', $mapped['version']);
        $this->assertSame('EH', $mapped['energy']);
        $this->assertSame(50, $mapped['mileage']);
        $this->assertSame(2024, $mapped['year']);
        $this->assertSame(33500.0, $mapped['price']);
        $this->assertFalse($mapped['is_deleted']);
    }

    public function test_it_prefers_the_main_picture(): void
    {
        $mapped = (new AdvertMapper)->map($this->adverts()[0]);

        $this->assertStringEndsWith('8481-1.jpg', $mapped['main_picture_url']);
        $this->assertCount(2, $mapped['pictures']);
    }

    public function test_it_falls_back_on_merchant_price_first_picture_and_registration_year(): void
    {
        $mapped = (new AdvertMapper)->map($this->adverts()[1]);

        $this->assertSame(17990.0, $mapped['price']);
        $this->assertStringEndsWith('9000-1.jpg', $mapped['main_picture_url']);
        $this->assertSame(2022, $mapped['year']);
    }

    public function test_brand_case_is_normalized(): void
    {
        $mapper = new AdvertMapper;

        $this->assertSame('Renault', $mapper->map($this->adverts()[2])['brand']);
        $this->assertSame('BMW', $mapper->map(['reference' => '1', 'vehicle' => ['manufacturer' => 'bmw']])['brand']);
        $this->assertSame('Citroën', $mapper->map(['reference' => '2', 'vehicle' => ['manufacturer' => 'CITROËN']])['brand']);
    }

    public function test_uppercase_models_are_normalized_but_codes_are_kept(): void
    {
        $mapper = new AdvertMapper;
        $model = fn (string $m) => $mapper->map(['reference' => '1', 'vehicle' => ['manufacturer' => 'X', 'model' => $m]])['model'];

        $this->assertSame('Scudo', $model('SCUDO'));
        $this->assertSame('Model Y', $model('MODEL Y'));
        $this->assertSame('Yaris Cross', $model('YARIS CROSS'));
        $this->assertSame('Austral', $model('Austral'));
        $this->assertSame('C3', $model('C3'));
        $this->assertSame('ID.3', $model('ID.3'));
        $this->assertSame('CX-5', $model('CX-5'));
    }

    public function test_deleted_adverts_are_flagged(): void
    {
        $this->assertTrue((new AdvertMapper)->map($this->adverts()[3])['is_deleted']);
    }

    public function test_it_rejects_adverts_without_reference_or_brand(): void
    {
        $mapper = new AdvertMapper;

        $this->assertNull($mapper->map(['vehicle' => ['manufacturer' => 'Renault']]));
        $this->assertNull($mapper->map(['reference' => '1', 'vehicle' => []]));
    }

    public function test_energy_labels(): void
    {
        $this->assertSame('Diesel', AdvertMapper::energyLabel('GO'));
        $this->assertSame('XX', AdvertMapper::energyLabel('XX'));
        $this->assertNull(AdvertMapper::energyLabel(null));
    }
}
