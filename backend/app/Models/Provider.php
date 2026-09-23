<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\AccountStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Provider extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'user_id',
        'full_name',
        'birth_date',
        'wilaya_id',
        'commune_id',
        'coverage_radius_km',
        'is_available',
        'current_lat',
        'current_lng',
        'rating_avg',
        'rating_count',
        'status',
        'trial_ends_at',
        'profile_photo',
        'internal_note',
    ];

    protected $hidden = [
        'internal_note',
    ];

    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'is_available' => 'boolean',
            'rating_avg' => 'float',
            'rating_count' => 'integer',
            'status' => AccountStatus::class,
            'trial_ends_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function wilaya(): BelongsTo
    {
        return $this->belongsTo(Wilaya::class);
    }

    public function commune(): BelongsTo
    {
        return $this->belongsTo(Commune::class);
    }

    public function services(): BelongsToMany
    {
        return $this->belongsToMany(Service::class, 'provider_services')
            ->withPivot('indicative_price', 'currency')
            ->withTimestamps();
    }

    public function coverageWilayas(): BelongsToMany
    {
        return $this->belongsToMany(Wilaya::class, 'provider_wilaya')
            ->withTimestamps();
    }

    public function documents(): HasMany
    {
        return $this->hasMany(ProviderDocument::class);
    }

    public function vehicles(): HasMany
    {
        return $this->hasMany(Vehicle::class);
    }

    public function activeVehicle(): ?Vehicle
    {
        return $this->vehicles()->where('is_active', true)->first();
    }

    public function requests(): HasMany
    {
        return $this->hasMany(ServiceRequest::class, 'provider_id');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(RequestAssignment::class);
    }

    public function hasActiveIntervention(): bool
    {
        return $this->requests()
            ->whereIn('status', [
                \App\Enums\RequestStatus::Searching->value,
                \App\Enums\RequestStatus::Accepted->value,
                \App\Enums\RequestStatus::EnRoute->value,
                \App\Enums\RequestStatus::OnSite->value,
            ])
            ->exists();
    }

    public function coversService(int $serviceId): bool
    {
        return $this->services()->where('service_id', $serviceId)->exists();
    }

    /**
     * Distance in kilometers from a coordinate (haversine).
     */
    public static function scopeNear(\Illuminate\Database\Eloquent\Builder $query, float $lat, float $lng, int $radiusKm): \Illuminate\Database\Eloquent\Builder
    {
        $latRad = deg2rad((float) $lat);
        $lngRad = deg2rad((float) $lng);

        return $query
            ->whereNotNull('current_lat')
            ->whereNotNull('current_lng')
            ->select('providers.*')
            ->selectRaw(
                '(6371 * acos(least(1.0, cos(?)*cos(radians(current_lat))*cos(radians(current_lng) - ?) + sin(?)*sin(radians(current_lat))))) AS distance_km',
                [$latRad, $lngRad, $latRad]
            )
            ->having('distance_km', '<=', $radiusKm)
            ->orderBy('distance_km');
    }
}