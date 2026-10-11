<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            $table->string('title')->nullable();
            $table->string('brand')->index();
            $table->string('model')->nullable();
            $table->string('version')->nullable();
            $table->string('offer_type', 8)->nullable();
            $table->string('energy', 4)->nullable();
            $table->string('gearbox', 4)->nullable();
            $table->unsignedInteger('mileage')->nullable();
            $table->unsignedSmallInteger('year')->nullable();
            $table->unsignedSmallInteger('power')->nullable();
            $table->string('color')->nullable();
            $table->decimal('price', 10, 2)->nullable();
            $table->decimal('catalog_price', 10, 2)->nullable();
            $table->string('main_picture_url', 2048)->nullable();
            $table->json('pictures')->nullable();
            $table->string('url', 2048)->nullable();
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};
