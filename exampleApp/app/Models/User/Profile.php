<?php

namespace App\Models\User;

use App\Models\User;
use App\Models\User\Profile\Avatar;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Profile extends Model
{
    use HasFactory;

    // one-to-one relation (inverse)
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // one-to-one relation
    public function avatar(): HasOne
    {
        return $this->hasOne(Avatar::class);
    }
}
