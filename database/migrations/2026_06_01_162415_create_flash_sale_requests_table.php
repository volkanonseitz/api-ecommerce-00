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
        Schema::create('flash_sale_requests', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->unsignedBigInteger('flash_sale_id');
            $table->json('requested_product_ids')->nullable();
            $table->boolean('request_status')->default(false);
            $table->string('note')->nullable();
            $table->softDeletes();
            $table->timestamps();
            $table->foreign('flash_sale_id')->references('id')->on('flash_sales')->onDelete('cascade');
            $table->string('language')->default(Config::get('app.locale'));
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('flash_sale_requests');
    }
};
