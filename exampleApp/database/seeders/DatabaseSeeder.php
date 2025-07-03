<?php

namespace Database\Seeders;

use App\Models\BlogPost;
use App\Models\BlogPost\Comment;
use App\Models\Tag;
use App\Models\User;
use App\Models\User\Like;
use App\Models\User\Profile\Avatar;
use App\Models\User\Profile\Avatar\Image;
use App\Models\User\Profile\Avatar\Video;
use Illuminate\Database\Seeder;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $userDatas = [
            ['modelFields' => ['name' => 'User with image avatar', 'email' => 'user-with-image-avatar@example.com'], 'avatarable' => Image::factory()->create()],
            ['modelFields' => ['name' => 'User with video avatar', 'email' => 'user-with-video-avatar@example.com'], 'avatarable' => Video::factory()->create()],
        ];

        foreach ($userDatas as $userData) {
            User::factory()
                ->hasProfile()
                ->hasBlogPosts(4)
                ->hasTags(1)
                ->hasRoles(2)
                ->create($userData['modelFields'])
                ->each(function ($user) use ($userData) {
                    Avatar::factory()->for($user->profile)->for($userData['avatarable'], 'avatarable')->create();

                    $user->blogPosts->each(
                        function (BlogPost $blogPost) use ($user) {
                            $blogPost->comments()->saveMany(Comment::factory()->count(3)->for($user)->make());
                            $blogPost->tags()->saveMany(Tag::all());
                            $blogPost->likes()->saveMany(Like::factory()->count(3)->for($user)->for($blogPost, 'likeable')->make());
                            $blogPost->comments->each(fn($comment) => $comment->likes()->saveMany(Like::factory()->count(3)->for($user)->for($comment, 'likeable')->make()));
                        }
                    );
                });
        }
    }
}
