<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\BlogPost\Comment;
use App\Models\User\Like;
use App\Models\User\Profile;
use App\Models\User\Profile\Avatar;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // one-to-one relation
    public function profile(): HasOne
    {
        return $this->hasOne(Profile::class);
    }

    // one-to-many relation
    public function blogPosts(): HasMany
    {
        return $this->hasMany(BlogPost::class);
    }

    // one-of-many relation
    public function latestBlogPost(): HasOne
    {
        return $this->hasOne(BlogPost::class)->latestOfMany();
    }

    // has-one-through relation
    public function avatar(): HasOneThrough
    {
        return $this->hasOneThrough(Avatar::class, Profile::class);
    }

    // has-many-through relation
    public function comments(): HasManyThrough
    {
        return $this->hasManyThrough(Comment::class, BlogPost::class);
    }

    // many-to-many relation
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class);
    }

    // one-to-many relation
    public function likes(): HasMany
    {
        return $this->hasMany(Like::class);
    }

    // one-of-many relation
    public function latestLike(): HasOne
    {
        return $this->HasOne(Like::class)->latestOfMany();
    }

    // polymorphic many-to-many relation
    public function tags(): MorphToMany
    {
        return $this->morphToMany(Tag::class, 'taggable');
    }
}
