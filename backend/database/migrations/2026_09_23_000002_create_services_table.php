<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->string('code', 10)->unique();
            $table->string('name_fr', 100);
            $table->string('name_ar', 100)->nullable();
            $table->string('type', 20)->default(\App\Enums\ServiceType::Emergency->value);
            $table->text('description_fr')->nullable();
            $table->text('description_ar')->nullable();
            $table->boolean('active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};