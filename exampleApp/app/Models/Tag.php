<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class Tag extends Model
{
    use HasFactory;

    // polymorphic many-to-many relation (inverse)
    public function blogPosts(): MorphToMany
    {
        return $this->morphedByMany(BlogPost::class, 'taggable');
    }

    // polymorphic many-to-many relation (inverse)
    public function users(): MorphToMany
    {
        return $this->morphedByMany(User::class, 'taggable');
    }
}
