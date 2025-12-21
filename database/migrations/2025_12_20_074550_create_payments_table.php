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
        Schema::create('payments', function (\Illuminate\Database\Schema\Blueprint $table) {
            $table->id();
            $table->foreignId('upload_id')->constrained('payment_uploads')->cascadeOnDelete();

            $table->string('customer_id', 50);
            $table->string('customer_name');
            $table->string('customer_email');

            // store money safely: use DECIMAL not float
            $table->decimal('amount', 18, 6);
            $table->string('currency', 3);

            $table->decimal('usd_amount', 18, 6);
            $table->decimal('usd_rate', 18, 10)->nullable(); // rate used for conversion

            $table->string('reference_no', 80)->index();
            $table->dateTime('payment_at')->nullable();

            $table->timestamps();

            // Prevent duplicates for same file
            $table->unique(['upload_id', 'reference_no']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
