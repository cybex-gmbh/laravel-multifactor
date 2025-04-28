<?php

namespace CybexGmbh\LaravelMultiFactor\Models;

use App\Models\User;
use CybexGmbh\LaravelMultiFactor\Enums\MultiFactorAuthMethod as TwoFactorAuthMethodEnum;
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
        'type' => TwoFactorAuthMethodEnum::class,
    ];

    /**
     * @return BelongsToMany
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'multi_factor_auth_method_user');
    }
}
