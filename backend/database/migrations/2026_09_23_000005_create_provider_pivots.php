<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('provider_services', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('provider_id')->constrained('providers')->cascadeOnDelete();
            $table->foreignId('service_id')->constrained('services')->cascadeOnDelete();
            $table->decimal('indicative_price', 10, 2)->nullable();
            $table->string('currency', 3)->default('DZD');
            $table->timestamps();

            $table->unique(['provider_id', 'service_id']);
        });

        Schema::create('provider_wilaya', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('provider_id')->constrained('providers')->cascadeOnDelete();
            $table->foreignId('wilaya_id')->constrained('wilayas')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['provider_id', 'wilaya_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('provider_wilaya');
        Schema::dropIfExists('provider_services');
    }
};