<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Config;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('variation_options', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->json('image')->nullable();
            $table->boolean('is_digital')->default(0);
            $table->string('price');
            $table->string('sale_price')->nullable();
            $table->string('language')->default(Config::get('app.locale'));
            $table->unsignedBigInteger('quantity');
            $table->integer('sold_quantity')->default(0);
            $table->boolean('is_disable')->default(false);
            $table->string('sku')->nullable();
            $table->json('options');
            $table->unsignedBigInteger('product_id')->nullable();
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('variation_options');
    }
};
