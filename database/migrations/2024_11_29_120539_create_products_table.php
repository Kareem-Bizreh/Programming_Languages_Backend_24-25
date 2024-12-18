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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('market_id')->constrained('markets')->references('id')->cascadeOnDelete();
            $table->foreignId('category_id')->constrained('categories')->references('id')->cascadeOnDelete();
            $table->string('name_en');
            $table->string('name_ar');
            $table->unsignedInteger('quantity');
            $table->unsignedInteger('price');
            $table->string('image')->nullable();
            $table->string('description_en')->nullable();
            $table->string('description_ar')->nullable();
            $table->unsignedInteger('number_of_purchases')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
