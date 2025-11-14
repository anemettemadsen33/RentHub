<?php

namespace Tests\Unit\Services;

use App\Services\CurrencyService;
use App\Models\Currency;
use App\Models\ExchangeRate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CurrencyServiceTest extends TestCase
{
    use RefreshDatabase;

    private CurrencyService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(CurrencyService::class);
    }

    public function test_can_convert_currency(): void
    {
        // Arrange
        $usd = Currency::factory()->create([
            'code' => 'USD',
            'symbol' => '$',
            'name' => 'US Dollar',
        ]);

        $eur = Currency::factory()->create([
            'code' => 'EUR',
            'symbol' => '€',
            'name' => 'Euro',
        ]);

        ExchangeRate::factory()->create([
            'from_currency_id' => $usd->id,
            'to_currency_id' => $eur->id,
            'rate' => 0.85,
        ]);

        // Act
        $result = $this->service->convert(100, 'USD', 'EUR');

        // Assert
        $this->assertEquals(85.0, $result);
    }

    public function test_conversion_returns_same_amount_for_same_currency(): void
    {
        // Arrange
        $usd = Currency::factory()->create(['code' => 'USD']);

        // Act
        $result = $this->service->convert(100, 'USD', 'USD');

        // Assert
        $this->assertEquals(100, $result);
    }

    public function test_can_get_all_supported_currencies(): void
    {
        // Arrange
        Currency::factory()->count(3)->create();

        // Act
        $currencies = $this->service->getSupportedCurrencies();

        // Assert
        $this->assertCount(3, $currencies);
        $this->assertInstanceOf(Currency::class, $currencies->first());
    }

    public function test_can_get_default_currency(): void
    {
        // Arrange
        Currency::factory()->create([
            'code' => 'USD',
            'is_default' => true,
        ]);

        // Act
        $currency = $this->service->getDefaultCurrency();

        // Assert
        $this->assertNotNull($currency);
        $this->assertEquals('USD', $currency->code);
        $this->assertTrue($currency->is_default);
    }

    public function test_can_format_amount_with_currency(): void
    {
        // Arrange
        $usd = Currency::factory()->create([
            'code' => 'USD',
            'symbol' => '$',
        ]);

        // Act
        $formatted = $this->service->format(1234.56, 'USD');

        // Assert
        $this->assertStringContainsString('1,234.56', $formatted);
        $this->assertStringContainsString('$', $formatted);
    }
}
