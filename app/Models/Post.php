<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        "content",
        "image_url"
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function likes()
    {
        return $this->hasMany(Like::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public static function getPosts(int $page, User $user)
    {
        $offset = ($page - 1) * 20;

        return Post::with(["user", "likes"])->withCount("likes")
            ->withCount(["likes as liked" => function (Builder $query) use ($user) {
                return $query->where("user_id", $user->id);
            }])->orderBy("updated_at", "DESC")->offset($offset)->limit(30)->get();
    }
}
