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
        if (env('TRANSLATION_ENABLED', false)) {
            Schema::create('languages', function (Blueprint $table) {
                $table->increments('id');
                $table->json('flag');
                $table->string('language_code');
                $table->string('language_name');
                $table->timestamps();
            });

            Schema::create('translations', function (Blueprint $table) {
                $table->id();
                $table->string('item_type');
                $table->unsignedBigInteger('item_id'); // this is the translated item id
                $table->unsignedBigInteger('translation_item_id')->nullable(); // this is the main element id
                $table->string('language_code');
                $table->string('source_language_code')->default(Config::get('app.locale'));
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('languages');
        Schema::dropIfExists('translations');
    }
};
