<?php

namespace Tests\Unit;

use App\Services\Glinche\VehicleDetailPresenter;
use PHPUnit\Framework\TestCase;

class VehicleDetailPresenterTest extends TestCase
{
    private const NBSP = "\u{00A0}";

    private function present(): array
    {
        $adverts = json_decode(file_get_contents(__DIR__.'/../Fixtures/vehicles.json'), true);

        return (new VehicleDetailPresenter)->present($adverts[0]);
    }

    private function flatten(array $detail): array
    {
        $rows = [];
        foreach ($detail['sections'] as $section) {
            foreach ($section['items'] as $item) {
                $rows[$item['label']] = $item['value'];
            }
        }

        return $rows;
    }

    public function test_it_formats_values_with_units_and_labels(): void
    {
        $rows = $this->flatten($this->present());

        $this->assertSame('SUV / Tout-terrain', $rows['Carrosserie']);
        $this->assertSame('50'.self::NBSP.'km (garanti)', $rows['Kilométrage']);
        $this->assertSame('200'.self::NBSP.'ch (147'.self::NBSP.'kW)', $rows['Puissance']);
        $this->assertSame('1'.self::NBSP.'199'.self::NBSP.'cm³', $rows['Cylindrée']);
        $this->assertSame('4,6'.self::NBSP.'L/100 km', $rows['Consommation mixte']);
        $this->assertSame("Crit'Air 1", $rows["Vignette Crit'Air"]);
        $this->assertSame('Mi-TEP / Mi-tissu', $rows['Sellerie']);
        $this->assertSame('24 mois, kilométrage illimité', $rows['Garantie']);
        $this->assertSame('Oui', $rows['Première main']);
        $this->assertSame('20/12/2024', $rows['Disponible depuis']);
    }

    public function test_sensitive_and_empty_fields_are_not_exposed(): void
    {
        $detail = $this->present();
        $json = json_encode($detail, JSON_UNESCAPED_UNICODE);

        $this->assertStringNotContainsString('VF1RHN01372960594', $json);
        $this->assertStringNotContainsString('GX-295-DK', $json);
        $this->assertStringNotContainsString('32500', $json);

        $this->assertNotContains('Électrique', array_column($detail['sections'], 'title'));
    }

    public function test_equipments_are_grouped_and_missing_ones_skipped(): void
    {
        $equipments = $this->present()['equipments'];

        $this->assertSame(
            ['Sécurité et aide à la conduite', 'Confort et multimédia', 'Autres'],
            array_column($equipments, 'category'),
        );
        $this->assertSame(['Caméra de recul', 'Radar avant'], $equipments[1]['items'][0]['details']);
        $this->assertStringNotContainsString('Toit ouvrant', json_encode($equipments, JSON_UNESCAPED_UNICODE));
    }

    public function test_options_and_price_notes(): void
    {
        $detail = $this->present();

        $this->assertSame('680'.self::NBSP.'€ HT', $detail['options'][0]['price']);
        $this->assertNull($detail['options'][1]['price']);
        $this->assertSame(['TVA 20 %', 'TVA récupérable'], $detail['price_notes']);
    }

    public function test_an_empty_advert_gives_an_empty_detail(): void
    {
        $this->assertSame(
            ['sections' => [], 'equipments' => [], 'options' => [], 'price_notes' => []],
            (new VehicleDetailPresenter)->present([]),
        );
    }
}
