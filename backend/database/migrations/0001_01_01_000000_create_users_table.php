<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('phone_number', 20)->unique();
            $table->string('country_code', 5)->default('+213');
            $table->enum('role_type', \App\Enums\UserRole::values())->default(\App\Enums\UserRole::Client->value);
            $table->string('email')->nullable()->unique();
            $table->string('password')->nullable(); // admin auth only
            $table->timestamp('phone_verified_at')->nullable();
            $table->timestamp('last_login_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};