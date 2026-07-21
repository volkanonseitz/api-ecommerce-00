<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Hanya tambahkan FULLTEXT index jika driver database adalah MySQL atau MariaDB
        if (DB::getDriverName() == 'mysql') {
            Schema::table('orders', function (Blueprint $table) {
                // Konversi ke TEXT jika perlu, atau pastikan sudah TEXT/VARCHAR yang panjang
                $table->fullText('tracking_number');
            });

            Schema::table('users', function (Blueprint $table) {
                // Pastikan kolom name dan email cukup panjang untuk FULLTEXT index
                $table->fullText(['name', 'email']);
            });
        }
        // ponytail: SQLite tidak mendukung FULLTEXT index via Schema Builder.
        // Implementasi ini hanya untuk MySQL/MariaDB. Untuk SQLite,
        // pencarian akan tetap menggunakan LIKE atau memerlukan FTS.
    }

    public function down(): void
    {
        if (DB::getDriverName() == 'mysql') {
            Schema::table('orders', function (Blueprint $table) {
                $table->dropFullText('tracking_number');
            });

            Schema::table('users', function (Blueprint $table) {
                $table->dropFullText(['name', 'email']);
            });
        }
    }
};
