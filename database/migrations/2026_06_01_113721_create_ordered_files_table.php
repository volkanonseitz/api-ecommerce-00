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
        Schema::create('ordered_files', function (Blueprint $table) {
            $table->id();
            $table->string('purchase_key');
            $table->unsignedBigInteger('digital_file_id');
            $table->foreign('digital_file_id')->references('id')->on('digital_files')->onDelete('cascade');
            $table->string('tracking_number')->nullable();
            $table->foreign('tracking_number')->references('tracking_number')->on('orders')->onDelete('cascade');
            $table->unsignedBigInteger('customer_id');
            $table->foreign('customer_id')->references('id')->on('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ordered_files');
    }
};
