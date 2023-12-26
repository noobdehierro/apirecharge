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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('status');
            $table->string('sales_type');
            $table->string('offering_id');
            $table->string('offering_name')->nullable();
            $table->string('msisdn')->nullable();
            $table->decimal('amount')->default(0);
            $table->string('me_reference_id')->nullable();
            $table->string('payment_method')->nullable();
            $table->string('payment_id')->nullable();
            $table->string('reference_id')->nullable();
            $table->string('payment_request_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
