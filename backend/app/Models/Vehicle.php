<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\VehicleType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Vehicle extends Model
{
    use HasFactory;

    protected $fillable = [
        'provider_id',
        'vehicle_type',
        'brand',
        'model',
        'plate',
        'year',
        'is_active',
    ];

    protected $hidden = ['pivot'];

    protected function casts(): array
    {
        return [
            'vehicle_type' => VehicleType::class,
            'is_active' => 'boolean',
            'year' => 'integer',
        ];
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }
}