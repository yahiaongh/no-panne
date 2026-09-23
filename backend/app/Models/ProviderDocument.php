<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ProviderDocumentType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProviderDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'provider_id',
        'type',
        'path',
        'original_name',
        'mime_type',
        'size',
        'verification_status',
        'rejection_reason',
        'verified_at',
    ];

    protected function casts(): array
    {
        return [
            'type' => ProviderDocumentType::class,
            'verified_at' => 'datetime',
        ];
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }
}