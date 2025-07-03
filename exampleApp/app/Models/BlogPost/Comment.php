<?php

namespace App\Models\BlogPost;

use App\Models\BlogPost;
use App\Models\User;
use App\Models\User\Like;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Comment extends Model
{
    use HasFactory;

    // user relation
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // one-to-many relation (inverse)
    public function blogPost(): BelongsTo
    {
        return $this->belongsTo(BlogPost::class);
    }

    // polymorphic one-to-many relation (inverse)
    public function likes(): MorphMany
    {
        return $this->morphMany(Like::class, 'likeable');
    }
}
