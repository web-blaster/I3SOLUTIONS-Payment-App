<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('exchange_rates', function (Blueprint $table) {
            $table->id()
                ->comment('Primary key');

            $table->char('base_currency', 3)
                ->comment('Base currency code (ISO 4217), e.g. EUR, GBP, LKR');

            $table->char('quote_currency', 3)
                ->comment('Quote currency code (ISO 4217), fixed to USD');

            $table->decimal('rate', 20, 10)
                ->comment('Exchange rate from base_currency to quote_currency with high precision');

            $table->timestamp('as_of')
                ->comment('Date/time for which this exchange rate is valid (from provider or refresh time)');

            $table->timestamps(); // created_at, updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exchange_rates');
    }
};
