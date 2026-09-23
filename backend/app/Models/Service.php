<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ServiceType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Service extends Model
{
    use HasFactory;

    protected $hidden = ['pivot'];

    protected $fillable = [
        'code',
        'name_fr',
        'name_ar',
        'type',
        'description_fr',
        'description_ar',
        'active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'type' => ServiceType::class,
            'active' => 'boolean',
        ];
    }

    public function providers(): BelongsToMany
    {
        return $this->belongsToMany(Provider::class, 'provider_services')
            ->withPivot('indicative_price', 'currency');
    }
}