<?php

use App\Enums\RequestStatus;
use App\Enums\RequestType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('requests', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('client_id')->constrained('clients')->cascadeOnDelete();
            $table->foreignUuid('provider_id')->nullable()->constrained('providers')->nullOnDelete();
            $table->foreignId('service_id')->constrained('services');
            $table->string('type', 20)->default(RequestType::Emergency->value);
            $table->text('description')->nullable();
            $table->string('client_address', 500);
            $table->decimal('client_lat', 10, 7);
            $table->decimal('client_lng', 10, 7);
            $table->decimal('estimated_budget', 10, 2)->nullable();
            $table->timestamp('scheduled_at')->nullable();
            $table->decimal('amount_cash', 10, 2)->nullable();
            $table->string('status', 24)->default(RequestStatus::Searching->value);
            $table->string('cancellation_reason', 255)->nullable();
            $table->string('cancelled_by', 20)->nullable();
            $table->unsignedSmallInteger('search_radius_km')->default(10);
            $table->timestamp('response_deadline_at')->nullable();
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('arrived_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('archived_at')->nullable();
            $table->text('provider_internal_note')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index('type');
            $table->index(['client_id', 'status']);
            $table->index(['provider_id', 'status']);
            $table->index('created_at');
            $table->index('response_deadline_at');
        });

        Schema::create('request_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('request_id')->constrained('requests')->cascadeOnDelete();
            $table->string('path', 500);
            $table->string('mime_type', 100)->nullable();
            $table->unsignedInteger('size')->nullable();
            $table->timestamps();

            $table->index('request_id');
        });

        Schema::create('request_status_history', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('request_id')->constrained('requests')->cascadeOnDelete();
            $table->string('from_status', 24)->nullable();
            $table->string('to_status', 24);
            $table->string('changed_by_type', 20)->default('system');
            $table->foreignUuid('changed_by_id')->nullable();
            $table->string('note', 500)->nullable();
            $table->timestamps();

            $table->index(['request_id', 'created_at']);
        });

        Schema::create('request_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('request_id')->constrained('requests')->cascadeOnDelete();
            $table->foreignUuid('provider_id')->constrained('providers')->cascadeOnDelete();
            $table->string('status', 20)->default('invited'); // invited | accepted | rejected | expired
            $table->timestamp('invited_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('responded_at')->nullable();
            $table->timestamps();

            $table->unique(['request_id', 'provider_id']);
            $table->index(['provider_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('request_assignments');
        Schema::dropIfExists('request_status_history');
        Schema::dropIfExists('request_photos');
        Schema::dropIfExists('requests');
    }
};