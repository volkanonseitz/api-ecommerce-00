<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Config;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('tracking_number')->unique();
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->string('customer_contact');
            $table->string('customer_name')->nullable();
            $table->double('amount');
            $table->double('sales_tax')->nullable();
            $table->double('paid_total')->nullable();
            $table->double('total')->nullable();
            $table->longText('note')->nullable();
            $table->string('language')->default(Config::get('app.locale'));
            $table->decimal('cancelled_amount')->default(0);
            $table->decimal('cancelled_tax')->default(0);
            $table->decimal('cancelled_delivery_fee')->default(0);
            $table->unsignedBigInteger('coupon_id')->nullable();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->unsignedBigInteger('shop_id')->nullable();
            $table->double('discount')->nullable();
            $table->string('payment_gateway')->nullable();
            $table->string('altered_payment_gateway')->nullable();
            $table->json('shipping_address')->nullable();
            $table->json('billing_address')->nullable();
            $table->unsignedBigInteger('logistics_provider')->nullable();
            $table->double('delivery_fee')->nullable();
            $table->string('delivery_time')->nullable();
            $table->enum('order_status', OrderStatus::getValues())->default(OrderStatus::PENDING);
            $table->enum('payment_status', PaymentStatus::getValues())->default(PaymentStatus::PENDING);
            $table->softDeletes();
            $table->timestamps();
            $table->foreign('customer_id')->references('id')->on('users');
            $table->foreign('shop_id')->references('id')->on('shops')->onDelete('cascade');
            $table->foreign('parent_id')->references('id')->on('orders')->onDelete('cascade');
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
