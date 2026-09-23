<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\RequestStatus;
use App\Enums\RequestType;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ServiceRequest extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'requests';

    protected $fillable = [
        'client_id',
        'provider_id',
        'service_id',
        'type',
        'description',
        'client_address',
        'client_lat',
        'client_lng',
        'estimated_budget',
        'scheduled_at',
        'amount_cash',
        'status',
        'cancellation_reason',
        'cancelled_by',
        'search_radius_km',
        'response_deadline_at',
        'accepted_at',
        'arrived_at',
        'completed_at',
        'archived_at',
        'provider_internal_note',
    ];

    protected $hidden = [
        'provider_internal_note',
    ];

    protected function casts(): array
    {
        return [
            'type' => RequestType::class,
            'status' => RequestStatus::class,
            'client_lat' => 'float',
            'client_lng' => 'float',
            'estimated_budget' => 'float',
            'amount_cash' => 'float',
            'scheduled_at' => 'datetime',
            'response_deadline_at' => 'datetime',
            'accepted_at' => 'datetime',
            'arrived_at' => 'datetime',
            'completed_at' => 'datetime',
            'archived_at' => 'datetime',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function photos(): HasMany
    {
        return $this->hasMany(RequestPhoto::class, 'request_id');
    }

    public function statusHistory(): HasMany
    {
        return $this->hasMany(RequestStatusHistory::class, 'request_id');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(RequestAssignment::class, 'request_id');
    }

    public function review(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Review::class, 'request_id');
    }

    public function isActive(): bool
    {
        return ! $this->status->isTerminal();
    }
}