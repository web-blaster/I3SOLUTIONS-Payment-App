<?php

// app/Console/Commands/RefreshExchangeRates.php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class RefreshExchangeRates extends Command
{
    protected $signature = 'fx:refresh {--quote=USD}';
    protected $description = 'Refresh cached FX rates (base->quote)';

    public function handle(): int
    {
        $quote = strtoupper($this->option('quote') ?? 'USD');

        $baseUrl = rtrim(config('services.exchange.base_url'), '/');
        $timeout = (int) config('services.exchange.timeout', 8);

        // Example: pull all rates with base=USD, then invert (base->USD) if needed
        // NOTE: exchangerate.host supports base=USD; if provider differs, adjust.
        $resp = Http::timeout($timeout)->retry(2, 250)->get("{$baseUrl}/latest", [
            'base' => $quote,  // base=USD gives USD->XXX
        ]);

        if (!$resp->ok()) {
            $this->error("FX API failed HTTP {$resp->status()}");
            return self::FAILURE;
        }

        $json = $resp->json();
        $rates = $json['rates'] ?? [];
        $asOf  = $json['date'] ?? now()->toDateString();

        if (!is_array($rates) || empty($rates)) {
            $this->error("FX API returned empty rates");
            return self::FAILURE;
        }

        $now = now();

        // We need BASE->USD. If API gives USD->BASE, then:
        // rate(BASE->USD) = 1 / rate(USD->BASE)
        $rows = [];
        foreach ($rates as $baseCurrency => $usdToBase) {
            $baseCurrency = strtoupper($baseCurrency);
            if ($baseCurrency === $quote) continue;

            if (!is_numeric($usdToBase) || (float)$usdToBase == 0.0) continue;

            // Use bc math to avoid float; convert to string
            $rate = bcdiv('1', (string)$usdToBase, 10);

            $rows[] = [
                'base_currency' => $baseCurrency,
                'quote_currency' => $quote,
                'rate' => $rate,
                'as_of' => $asOf . ' 00:00:00',
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }


        // Upsert is better than update+insert (no gap period)
        DB::transaction(function () use ($rows) {
            DB::table('exchange_rates')->upsert(
                $rows,
                ['base_currency', 'quote_currency'],
                ['rate', 'as_of', 'updated_at']
            );
        });

        $this->info('FX rates refreshed: ' . count($rows));
        return self::SUCCESS;
    }
}
