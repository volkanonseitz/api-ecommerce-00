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
        Schema::create('store_notice_shop', function (Blueprint $table) {
            $table->foreignId('store_notice_id')->nullable()->references('id')->on('store_notices')->cascadeOnDelete();
            $table->foreignId('shop_id')->nullable()->references('id')->on('shops')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('store_notice_shop');
    }
};
