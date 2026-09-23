<?php

use App\Enums\AccountStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->string('first_name', 100)->nullable();
            $table->string('last_name', 100)->nullable();
            $table->string('email', 255)->nullable();
            $table->foreignId('wilaya_id')->nullable()->constrained('wilayas')->nullOnDelete();
            $table->string('profile_photo', 500)->nullable();
            $table->string('language', 5)->default('fr');
            $table->string('status', 20)->default(AccountStatus::Active->value);
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index(['first_name', 'last_name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};