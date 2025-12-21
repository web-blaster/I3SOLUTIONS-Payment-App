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
        Schema::create('payment_row_logs', function (\Illuminate\Database\Schema\Blueprint $table) {
            $table->id();
            $table->foreignId('upload_id')->constrained('payment_uploads')->cascadeOnDelete();

            $table->unsignedInteger('row_number');
            $table->string('status')->index(); // SUCCESS|FAILED
            $table->string('reference_no', 80)->nullable();

            $table->text('message')->nullable();              // error or success note
            $table->json('raw')->nullable();                  // raw row data for debugging

            $table->timestamps();

            $table->index(['upload_id', 'row_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_row_logs');
    }
};
