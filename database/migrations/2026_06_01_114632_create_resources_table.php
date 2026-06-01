<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Config;
use App\Enums\ResourceType;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('resources', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug');
            $table->string('language')->default(Config::get('app.locale'));
            $table->string('icon')->nullable();
            $table->text('details')->nullable();
            $table->json('image')->nullable();
            $table->boolean('is_approved')->default(false);
            $table->double('price')->nullable();
            $table->enum('type', ResourceType::getValues());
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('resources');
    }
};
