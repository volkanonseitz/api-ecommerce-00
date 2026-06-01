<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Config;
use App\Enums\RefundPolicyTarget;
use App\Enums\RefundPolicyStatus;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('refund_policies', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->enum('target', RefundPolicyTarget::getValues())->default(RefundPolicyTarget::VENDOR);
            $table->string('language')->default(Config::get('app.locale'));
            $table->enum('status', RefundPolicyStatus::getValues())->default(RefundPolicyStatus::PENDING);
            $table->foreignId('shop_id')->nullable()->constrained('shops')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('refund_policies');
    }
};
