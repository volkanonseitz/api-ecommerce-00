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
        Schema::table('payment_methods', function (Blueprint $table) {
            // First check if columns exist before adding
            if (!Schema::hasColumn('payment_methods', 'method_type')) {
                $table->string('method_type')->nullable()->after('method_key');
            }
            
            if (Schema::hasColumn('payment_methods', 'default_card')) {
                $table->renameColumn('default_card', 'default_payment');
            }
            
            if (!Schema::hasColumn('payment_methods', 'brand')) {
                $table->string('brand')->nullable()->after('method_type');
            }
            
            if (!Schema::hasColumn('payment_methods', 'exp_month')) {
                $table->string('exp_month')->nullable()->after('last4');
            }
            
            if (!Schema::hasColumn('payment_methods', 'exp_year')) {
                $table->string('exp_year')->nullable()->after('exp_month');
            }
            
            if (!Schema::hasColumn('payment_methods', 'provider_data')) {
                $table->json('provider_data')->nullable();
            }
            
            if (!Schema::hasColumn('payment_methods', 'metadata')) {
                $table->json('metadata')->nullable();
            }
            
            if (!Schema::hasColumn('payment_methods', 'qris_url')) {
                $table->string('qris_url')->nullable();
            }
            
            if (!Schema::hasColumn('payment_methods', 'va_number')) {
                $table->string('va_number')->nullable();
            }
            
            if (!Schema::hasColumn('payment_methods', 'bank_code')) {
                $table->string('bank_code')->nullable();
            }
            
            if (!Schema::hasColumn('payment_methods', 'ewallet_type')) {
                $table->string('ewallet_type')->nullable();
            }
            
            if (!Schema::hasColumn('payment_methods', 'direct_debit_type')) {
                $table->string('direct_debit_type')->nullable();
            }
            
            if (!Schema::hasColumn('payment_methods', 'account_name')) {
                $table->string('account_name')->nullable();
            }
            
            if (!Schema::hasColumn('payment_methods', 'account_number')) {
                $table->string('account_number')->nullable();
            }
            
            if (!Schema::hasColumn('payment_methods', 'account_last4')) {
                $table->string('account_last4')->nullable();
            }
            
            if (!Schema::hasColumn('payment_methods', 'expiry_date')) {
                $table->timestamp('expiry_date')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payment_methods', function (Blueprint $table) {
            $columns = [
                'method_type',
                'brand',
                'exp_month',
                'exp_year',
                'provider_data',
                'metadata',
                'qris_url',
                'va_number',
                'bank_code',
                'ewallet_type',
                'direct_debit_type',
                'account_name',
                'account_number',
                'account_last4',
                'expiry_date'
            ];
            
            foreach ($columns as $column) {
                if (Schema::hasColumn('payment_methods', $column)) {
                    $table->dropColumn($column);
                }
            }
            
            if (Schema::hasColumn('payment_methods', 'default_payment')) {
                $table->renameColumn('default_payment', 'default_card');
            }
        });
    }
};
