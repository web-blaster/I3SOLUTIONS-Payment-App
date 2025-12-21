<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class ExchangeRateService
{


    public function rateToUsd(string $fromCurrency): string
    {
        $fromCurrency = strtoupper(trim($fromCurrency));
        if ($fromCurrency === 'USD') {
            return '1.0000000000';
        }

        /*
        |--------------------------------------------------------------------------
        | 1️⃣ Check database first
        |--------------------------------------------------------------------------
        */
        $row = DB::table('exchange_rates')
            ->where('base_currency', $fromCurrency)
            ->where('quote_currency', 'USD')
            ->orderByDesc('as_of')
            ->first();

        if ($row && isset($row->rate) && is_numeric($row->rate)) {
            // Normalize to 10 decimals
            return bcadd((string) $row->rate, '0', 10);
        }

        $cacheKey = "fx:{$fromCurrency}:USD";

        return Cache::remember($cacheKey, now()->addHours(6), function () use ($fromCurrency) {
            $baseUrl = rtrim(config('services.exchange.base_url'), '/');
            $timeout = (int) config('services.exchange.timeout', 8);

            $resp = Http::timeout($timeout)
                ->retry(2, 250) // small retry helps API hiccups
                ->get("{$baseUrl}/latest", [
                    'base' => $fromCurrency,
                    'symbols' => 'USD',
                ]);

            if (!$resp->ok()) {
                throw new \RuntimeException("Exchange API failed (HTTP {$resp->status()}).");
            }

            $json = $resp->json();

            $usd = data_get($json, 'rates.USD');

            // Ensure numeric-like string
            if ($usd === null || $usd === '' || !is_numeric($usd)) {
                throw new \RuntimeException('Invalid exchange rate response (rates.USD missing).');
            }

            // Keep as string, normalize to 10 decimals without float conversion
            return bcadd((string)$usd, '0', 10);
        });
    }
}
