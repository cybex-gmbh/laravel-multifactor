<?php

namespace App\Models\User;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Like extends Model
{
    use HasFactory;

    // one-to-many relation (inverse)
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // polymorphic one-to-many relation (inverse)
    public function likeable(): MorphTo
    {
        return $this->morphTo();
    }
}
