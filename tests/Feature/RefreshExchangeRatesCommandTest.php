<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class RefreshExchangeRatesCommandTest extends TestCase
{
    public function test_refreshes_exchange_rates_into_database(): void
    {
        // Fake the external API
        Http::fake([
            'api.exchangerate.host/*' => Http::response([
                'rates' => [
                    'EUR' => 0.8,
                    'GBP' => 0.5,
                ],
                'date' => now()->toDateString(),
            ], 200),
        ]);

        // Run the artisan command
        $this->artisan('fx:refresh --quote=USD')
            ->expectsOutputToContain('FX rates refreshed')
            ->assertExitCode(0);

        // Assert DB has rows
        $count = DB::table('exchange_rates')->count();
        $this->assertGreaterThan(0, $count);
    }
}
