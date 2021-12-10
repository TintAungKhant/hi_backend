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
        'gender',
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

    public function profile_images()
    {
        return $this->hasMany(ProfileImage::class);
    }

    public function conversations()
    {
        return $this->belongsToMany(Conversation::class);
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    public function getNewContacts(?int $gender = null, int $limit = 20)
    {
        return $this->whereNotIn("id", $this->contacts->pluck("id"))->where(function ($q) use ($gender) {
            if ($gender) {
                $q->where("gender", $gender);
            }
        })->inRandomOrder()->take($limit)->get();
    }

    public function getContacts(?int $type = null, int $page = 1, int $limit = 20)
    {
        $offset = ($page - 1) * $limit;

        $contacts =  $this->load([
            "contacts" => function ($q) use ($offset, $limit, $type) {
                if ($type == "online" || $type == "offline") {
                    $q->wherePivot("type", self::CONTACT_USER_TYPES["friend"])->where(function ($q2) use ($type) {
                        if ($type == "online") {
                            $q2->where("last_seen", ">=", \Carbon\Carbon::now()->subMinutes(2));
                        } else if ($type == "offline") {
                            $q2->where("last_seen", "<=", \Carbon\Carbon::now()->subMinutes(2))->orWhere("last_seen", null);
                        }
                    });
                } else if ($type == "request") {
                    $q->wherePivot("type", self::CONTACT_USER_TYPES["deciding"]);
                } else if ($type == "added") {
                    $q->wherePivot("type", self::CONTACT_USER_TYPES["waiting"]);
                }
                $q->offset($offset)->limit($limit)->get();
            }
        ])->contacts;

        return $contacts->each->makeHidden('pivot');
    }

    public function getContact(User $user, ?int $pivot_type = null)
    {
        $contacts =  $this->load([
            "contacts" => function ($q) use ($pivot_type, $user) {
                if ($pivot_type) {
                    $q->wherePivot("type", $pivot_type);
                }
                $q->find($user->id);
            }
        ])->contacts;

        $contacts->each->makeHidden('pivot');

        return $contacts->first();
    }

    public function addContact(User $user)
    {
        $this->contacts()->attach(
            $user->id,
            [
                "type" => self::CONTACT_USER_TYPES["waiting"]
            ]
        );
        $user->contacts()->attach(
            $this->id,
            [
                "type" => self::CONTACT_USER_TYPES["deciding"]
            ]
        );
    }

    public function acceptContact(User $user)
    {
        $this->contacts()->sync(
            [
                $user->id => [
                    "type" => self::CONTACT_USER_TYPES["friend"]
                ]
            ],
            false
        );
        $user->contacts()->sync(
            [
                $this->id => [
                    "type" => self::CONTACT_USER_TYPES["friend"]
                ]
            ],
            false
        );
    }

    public function deleteContact(User $user)
    {
        $this->contacts()->detach($user->id);
        $user->contacts()->detach($this->id);
    }

    public function firstOrCreateConversation(User $user)
    {
        $contact = $this->contacts()->wherePivot("type", self::CONTACT_USER_TYPES["friend"])->where("users.id", $user->id)->first();
        if ($contact) {
            $conversations = $this->contacts()->wherePivot("type", self::CONTACT_USER_TYPES["friend"])->where("users.id", $user->id)->first()
                ->load(["conversations" => function ($q) {
                    $q->whereHas("users", function ($q2) {
                        $q2->where("users.id", $this->id);
                    })->has("users", "=", 2);
                }])->conversations->makeHidden("pivot");

            if (sizeof($conversations) == 0) {
                $conversation = Conversation::create([]);
                $conversation->users()->sync([$this->id, $user->id]);
            } else {
                $conversation = $conversations->first();
            }

            return $conversation;
        }

        return null;
    }

    public function getConversations(?Conversation $last_conversation = null, int $limit = 20)
    {
        return $this->load(["conversations" => function ($q) use ($last_conversation, $limit) {
            if ($last_conversation) {
                $q->where("conversations.id", "<>", $last_conversation->id);
                $q->where("conversations.updated_at", "<=", $last_conversation->created_at);
            }
            $q->with("latest_message")->whereHas("messages")->limit($limit)->orderBy("conversations.updated_at", "DESC");
        }])->conversations->makeHidden("pivot");
    }

    public function getConversation(int $id)
    {
        return $this->conversations()->where("id", $id)->first();
    }
}
