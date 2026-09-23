<?php

use App\Enums\AccountStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('providers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->string('full_name', 150);
            $table->date('birth_date')->nullable();
            $table->foreignId('wilaya_id')->constrained('wilayas');
            $table->foreignId('commune_id')->constrained('communes');
            $table->unsignedInteger('coverage_radius_km')->default(10);
            $table->boolean('is_available')->default(false);
            $table->decimal('current_lat', 10, 7)->nullable();
            $table->decimal('current_lng', 10, 7)->nullable();
            $table->decimal('rating_avg', 3, 2)->default(0);
            $table->unsignedInteger('rating_count')->default(0);
            $table->string('status', 20)->default(AccountStatus::Pending->value);
            $table->timestamp('trial_ends_at')->nullable();
            $table->string('profile_photo', 500)->nullable();
            $table->text('internal_note')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index('is_available');
            $table->index(['current_lat', 'current_lng']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('providers');
    }
};