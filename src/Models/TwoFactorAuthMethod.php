<?php

namespace CybexGmbh\LaravelTwoFactor\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Models\User;
use CybexGmbh\LaravelTwoFactor\Enums\TwoFactorAuthMethod as TwoFactorAuthMethodEnum;

class TwoFactorAuthMethod extends Model
{
    use HasFactory;

    protected $fillable = [
        'type'
    ];

    protected $casts = [
        'type' => TwoFactorAuthMethodEnum::class,
    ];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'two_factor_auth_method_user');
    }
}
