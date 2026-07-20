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
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'failed_login_attempts')) {
                $table->unsignedSmallInteger('failed_login_attempts')->default(0)->change();
            } else {
                $table->unsignedSmallInteger('failed_login_attempts')->default(0)->after('locked_until');
            }
            if (Schema::hasColumn('users', 'last_login_user_agent')) {
                $table->string('last_login_user_agent', 1000)->nullable()->change();
            } else {
                $table->string('last_login_user_agent', 1000)->nullable()->after('last_login_ip');
            }
        });

        Schema::create('password_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('password_hash');
            $table->timestamps();
        });

        // Add index for faster lookups
        Schema::table('password_history', function (Blueprint $table) {
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'locked_until',
                'failed_login_attempts',
                'last_login_at',
                'last_login_ip',
                'last_login_user_agent',
            ]);
        });

        Schema::dropIfExists('password_history');
    }
};
