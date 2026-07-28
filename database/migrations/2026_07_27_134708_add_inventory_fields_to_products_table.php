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
        Schema::table('products', function (Blueprint $table) {
            $table->integer('reserved_quantity')->default(0)->after('quantity');
            $table->integer('low_stock_threshold')->default(5)->after('reserved_quantity');
            $table->integer('available_quantity')->storedAs('quantity - reserved_quantity')->after('low_stock_threshold');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['reserved_quantity', 'low_stock_threshold', 'available_quantity']);
        });
    }
};
