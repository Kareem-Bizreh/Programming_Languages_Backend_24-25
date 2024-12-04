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
            $table->foreignId('user_id')->constrained('users')->references('id')->cascadeOnDelete();
            $table->foreignId('market_id')->nullable()->constrained('markets')->references('id')->cascadeOnDelete();
            $table->foreignId('status_id')->constrained('statuses')->references('id')->cascadeOnDelete();
            $table->foreignId('location_id')->constrained('locations')->references('id')->cascadeOnDelete();
            $table->foreignId('global_order_id')->nullable()->constrained('orders')->references('id')->cascadeOnDelete();
            $table->unsignedInteger('total_cost')->default(0);
            $table->unsignedInteger('count')->default(0);
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