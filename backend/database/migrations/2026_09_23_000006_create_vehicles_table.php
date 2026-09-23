<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('provider_id')->constrained('providers')->cascadeOnDelete();
            $table->string('vehicle_type', 20)->default(\App\Enums\VehicleType::Car->value);
            $table->string('brand', 100);
            $table->string('model', 100);
            $table->string('plate', 20)->unique();
            $table->unsignedSmallInteger('year')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['provider_id', 'is_active']);
        });

        // BRD RM-PR-05: a provider can have only one *active* vehicle.
        // Partial unique index (PostgreSQL). On other engines the equivalent
        // must be enforced in the application layer — see docs/architecture-decisions.md.
        DB::statement('CREATE UNIQUE INDEX vehicles_provider_active_unique ON vehicles (provider_id) WHERE is_active = true');
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};