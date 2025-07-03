<?php

namespace App\Models;

use App\Models\BlogPost\Comment;
use App\Models\User\Like;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class BlogPost extends Model
{
    use HasFactory;

    // one-to-many relation (inverse)
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // one-to-many relation
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    // polymorphic one-to-many relation (inverse)
    public function likes(): MorphMany
    {
        return $this->morphMany(Like::class, 'likeable');
    }

    // polymorphic one-of-many relation (inverse)
    public function latestLike(): MorphOne
    {
        return $this->morphOne(Like::class, 'likeable')->latestOfMany();
    }

    // polymorphic many-to-many relation
    public function tags(): MorphToMany
    {
        return $this->morphToMany(Tag::class, 'taggable');
    }
}
