<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('request_id')->unique()->constrained('requests')->cascadeOnDelete();
            $table->foreignUuid('client_id')->constrained('clients')->cascadeOnDelete();
            $table->foreignUuid('provider_id')->constrained('providers')->cascadeOnDelete();
            $table->unsignedTinyInteger('rating')->check('rating >= 1 AND rating <= 5');
            $table->string('comment', 500)->nullable();
            $table->string('moderation_status', 20)->default('visible'); // visible | hidden
            $table->string('hidden_reason', 255)->nullable();
            $table->timestamp('edited_at')->nullable();
            $table->timestamps();

            $table->index('provider_id');
            $table->index('moderation_status');
        });

        Schema::create('review_tags', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('review_id')->constrained('reviews')->cascadeOnDelete();
            $table->string('tag', 40);
            $table->timestamps();

            $table->unique(['review_id', 'tag']);
        });

        Schema::create('favorites', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('client_id')->constrained('clients')->cascadeOnDelete();
            $table->foreignUuid('provider_id')->constrained('providers')->cascadeOnDelete();
            $table->string('note', 255)->nullable();
            $table->timestamps();

            $table->unique(['client_id', 'provider_id']);
        });

        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('type', 60);
            $table->string('title', 255);
            $table->string('body', 500)->nullable();
            $table->json('data')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'read_at']);
            $table->index('created_at');
        });

        Schema::create('saved_addresses', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('client_id')->constrained('clients')->cascadeOnDelete();
            $table->string('label', 50);
            $table->string('address', 500);
            $table->decimal('lat', 10, 7)->nullable();
            $table->decimal('lng', 10, 7)->nullable();
            $table->timestamps();

            $table->index('client_id');
        });

        Schema::create('device_tokens', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('token', 500);
            $table->string('platform', 10)->default(\App\Enums\DevicePlatform::Android->value);
            $table->json('metadata')->nullable();
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'token']);
            $table->index('platform');
        });

        Schema::create('account_deletion_requests', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('reason', 500)->nullable();
            $table->timestamp('requested_at')->useCurrent();
            $table->timestamp('scheduled_deletion_at');
            $table->string('status', 20)->default('pending'); // pending | finalized | cancelled
            $table->timestamp('finalized_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'scheduled_deletion_at']);
        });

        Schema::create('audit_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignUuid('admin_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action', 100);
            $table->string('subject_type', 100)->nullable();
            $table->string('subject_id', 64)->nullable();
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->timestamps();

            $table->index(['subject_type', 'subject_id']);
            $table->index('created_at');
        });

        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key', 100)->unique();
            $table->json('value')->nullable();
            $table->string('group', 50)->default('general');
            $table->string('description', 255)->nullable();
            $table->timestamps();
        });

        Schema::create('otp_codes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('phone_number', 20);
            $table->string('code_hash', 255);
            $table->unsignedTinyInteger('attempts')->default(0);
            $table->unsignedTinyInteger('consecutive_failures')->default(0);
            $table->timestamp('expires_at');
            $table->timestamp('last_sent_at');
            $table->timestamp('blocked_until')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();

            $table->index(['phone_number', 'created_at']);
            $table->index('expires_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('otp_codes');
        Schema::dropIfExists('settings');
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('account_deletion_requests');
        Schema::dropIfExists('device_tokens');
        Schema::dropIfExists('saved_addresses');
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('favorites');
        Schema::dropIfExists('review_tags');
        Schema::dropIfExists('reviews');
    }
};