<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('provider_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('provider_id')->constrained('providers')->cascadeOnDelete();
            $table->string('type', 40);
            $table->string('path', 500);
            $table->string('original_name', 255)->nullable();
            $table->string('mime_type', 100)->nullable();
            $table->unsignedInteger('size')->nullable();
            $table->string('verification_status', 20)->default('pending'); // pending | approved | rejected
            $table->string('rejection_reason', 255)->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();

            $table->unique(['provider_id', 'type', 'original_name']);
            $table->index(['verification_status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('provider_documents');
    }
};