<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Enums\FlashSaleType;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('flash_sales', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->dateTime('start_date')->default(now());
            $table->dateTime('end_date');
            $table->boolean('sale_status')->default(false);
            $table->enum('type', FlashSaleType::getValues())->default(FlashSaleType::PERCENTAGE);
            $table->integer('rate')->nullable();
            $table->json('sale_builder')->nullable();
            $table->json('image')->nullable();
            $table->json('cover_image')->nullable();
            $table->string('language')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('flash_sales');
    }
};
