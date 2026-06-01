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
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();
            $table->string('method_key')->unique();
            $table->unsignedBigInteger('payment_gateway_id')->nullable();
            $table->boolean('default_card')->nullable()->default(false);
            $table->string('fingerprint')->unique();
            $table->string('owner_name')->nullable();
            $table->string('network')->nullable();
            $table->string('type')->nullable();
            $table->string('last4')->nullable();
            $table->string('expires')->nullable();
            $table->string('origin')->nullable();
            $table->string('verification_check')->nullable();
            $table->foreign('payment_gateway_id')->references('id')->on('payment_gateways')->onDelete('cascade');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_methods');
    }
};
