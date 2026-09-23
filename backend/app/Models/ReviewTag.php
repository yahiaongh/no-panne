<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ReviewTag as ReviewTagEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReviewTag extends Model
{
    use HasFactory;

    protected $fillable = ['review_id', 'tag'];

    protected function casts(): array
    {
        return [
            'tag' => ReviewTagEnum::class,
        ];
    }

    public function review(): BelongsTo
    {
        return $this->belongsTo(Review::class);
    }
}