<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\Http;
use App\Services\ExchangeRateService;

class ExchangeRateServiceTest extends TestCase
{
    public function test_it_falls_back_to_api_when_db_missing(): void
    {
        Http::fake([
            'api.exchangerate.host/*' => Http::response([
                'rates' => ['USD' => 1.5],
            ], 200),
        ]);

        $service = app(ExchangeRateService::class);

        $rate = $service->rateToUsd('GBP');

        $this->assertEquals('1.5000000000', $rate);
    }
}
