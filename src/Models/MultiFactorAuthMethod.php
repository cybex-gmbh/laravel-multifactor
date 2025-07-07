<?php

namespace Cybex\LaravelMultiFactor\Models;

use Illuminate\Foundation\Auth\User;
use Cybex\LaravelMultiFactor\Enums\MultiFactorAuthMethod as MultiFactorAuthMethodEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class MultiFactorAuthMethod extends Model
{
    use HasFactory;

    protected $fillable = [
        'type'
    ];

    protected $casts = [
        'type' => MultiFactorAuthMethodEnum::class,
    ];

    /**
     * @return BelongsToMany
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'multi_factor_auth_method_user');
    }
}
