<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Config;
use App\Enums\ProductType;
use App\Enums\ProductStatus;
use App\Enums\ProductVisibilityStatus;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->unsignedBigInteger('type_id');
            $table->foreign('type_id')->references('id')->on('types')->onDelete('cascade');
            $table->double('price')->nullable();
            $table->unsignedBigInteger('shop_id')->nullable();
            $table->foreign('shop_id')->references('id')->on('shops')->onDelete('cascade');
            $table->unsignedBigInteger('author_id')->nullable();
            $table->foreign('author_id')->references('id')->on('authors')->onDelete('cascade');
            $table->unsignedBigInteger('manufacturer_id')->nullable();
            $table->foreign('manufacturer_id')->references('id')->on('manufacturers')->onDelete('cascade');
            $table->boolean('is_digital')->default(0);
            $table->boolean('is_external')->default(0);
            $table->string('external_product_url')->nullable();
            $table->string('external_product_button_text')->nullable();
            $table->string('blocked_dates')->nullable();
            $table->double('sale_price')->nullable();
            $table->string('language')->default(Config::get('app.locale'));
            $table->float('min_price')->nullable();
            $table->float('max_price')->nullable();
            $table->string('sku')->nullable();
            $table->integer('quantity')->default(0);
            $table->integer('sold_quantity')->default(0);
            $table->boolean('in_stock')->default(true);
            $table->boolean('is_taxable')->default(false);
            $table->integer('in_flash_sale')->default(false);
            $table->unsignedBigInteger('shipping_class_id')->nullable();
            $table->foreign('shipping_class_id')->references('id')->on('shipping_classes');
            $table->enum('status', ProductStatus::getValues())->default(ProductStatus::DRAFT);
            $table->enum('visibility', ProductVisibilityStatus::getValues())->default(ProductVisibilityStatus::VISIBILITY_PUBLIC);
            $table->enum('product_type', ProductType::getValues())->default(ProductType::SIMPLE);
            $table->string('unit');
            $table->string('height')->nullable();
            $table->string('width')->nullable();
            $table->string('length')->nullable();
            $table->json('image')->nullable();
            $table->json('video')->nullable();
            $table->json('gallery')->nullable();
            $table->softDeletes();
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
