<?php

namespace App\Models\User\Profile;

use App\Models\User\Profile;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Avatar extends Model
{
    use HasFactory;

    // one-to-one relation (inverse)
    public function profile(): BelongsTo
    {
        return $this->belongsTo(Profile::class);
    }

    // polymorphic one-to-one relation
    public function avatarable(): MorphTo
    {
        return $this->morphTo();
    }
}
