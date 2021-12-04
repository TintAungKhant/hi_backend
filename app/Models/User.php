<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'name',
        'birthday',
        'avatar_url',
        'email',
        'password',
        'last_seen'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    const CONTACT_USER_TYPES = [
        "waiting" => 1,
        "deciding" => 2,
        "friend" => 3
    ];

    public function contacts()
    {
        return $this->belongsToMany(User::class, "contact_user", "user_id", "contact_user_id")->withPivot("type");
    }

    public function getNewContacts(?int $gender = null, int $limit = 20)
    {
        return $this->whereNotIn("id", $this->contacts->pluck("id"))->where(function ($q) use ($gender) {
            if ($gender) {
                $q->where("gender", $gender);
            }
        })->inRandomOrder()->take($limit)->get();
    }
}
