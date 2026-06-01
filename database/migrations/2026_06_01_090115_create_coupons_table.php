<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Config;
use App\Enums\CouponType;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->string('code');
            $table->string('language')->default(Config::get('app.locale'));
            $table->text('description')->nullable();
            $table->json('image')->nullable();
            $table->enum('type', CouponType::getValues())->default(CouponType::FIXED_COUPON);
            $table->float('amount')->default(0);
            $table->float('minimum_cart_amount')->default(0);
            $table->string('active_from');
            $table->string('expire_at');
            $table->boolean('target')->default(false)->comment('Default value is false but For authenticated customer the value is true');
            $table->boolean('is_approve')->default(false);
            $table->foreignId('shop_id')->nullable()->constrained('shops')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->timestamps();
            $table->timestamp('deleted_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coupons');
    }
};
