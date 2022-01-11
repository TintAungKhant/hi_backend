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

    protected $appends = ["online", "main_profile_image"];

    const CONTACT_USER_TYPES = [
        "waiting" => 1,
        "deciding" => 2,
        "friend" => 3
    ];

    public function getOnlineAttribute()
    {
        return $this->last_seen >= \Carbon\Carbon::now()->subMinutes(2);
    }

    public function getMainProfileImageAttribute()
    {
        $main_profile_image = null;
        if ($this->profile_images) {
            $this->profile_images->each(function ($image) use (&$main_profile_image) {
                if ($image->type == ProfileImage::PROFILE_IMAGE_TYPES["profile"]) {
                    $main_profile_image = $image;
                }
            });
        }
        return $main_profile_image;
    }

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

    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    public function likes()
    {
        return $this->hasMany(Like::class);
    }

    public function getNewContacts(?int $gender = null)
    {
        return $this->whereNotIn("id", array_merge($this->contacts->pluck("id")->toArray(), [$this->id]))->where(function ($q) use ($gender) {
            if ($gender) {
                $q->where("gender", $gender);
            }
        })->inRandomOrder()->take(30)->get();
    }

    public function getContacts(?string $type = null, int $page = 1)
    {
        $offset = ($page - 1) * 30;

        $contacts =  $this->load([
            "contacts" => function ($q) use ($offset, $type) {
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
                $q->offset($offset)->limit(30)->get();
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
        $contact = $this->contacts()->where("users.id", $user->id)->first();
        if ($contact) {
            $conversations = $contact->load(["conversations" => function ($q) {
                $q->whereHas("users", function ($q2) {
                    $q2->where("users.id", $this->id);
                })->has("users", "=", 2);
                $q->with(["messages" => function ($q3) {
                    $q3->with("messageable");
                }]);
            }])->conversations;

            $conversation = $conversations->first();
            if ($conversation) {
                $conversation->messages->each(function ($message) {
                    $message->messageable()->delete();
                    $message->messageable()->detach();
                    $message->delete();
                });
                $conversation->users()->detach([$user->id, $this->id]);
                $conversation->delete();
            }
        }

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

    public function getConversations(?Conversation $last_conversation = null)
    {
        return $this->load(["conversations" => function ($q) use ($last_conversation) {
            if ($last_conversation) {
                $q->where("conversations.id", "<>", $last_conversation->id);
                $q->where("conversations.updated_at", "<=", $last_conversation->updated_at);
            }
            $q->with("latest_message");
            $q->with("users")->whereHas("messages")->limit(30)->orderBy("conversations.updated_at", "DESC");
        }])->conversations->makeHidden("pivot");
    }

    public function getConversation(int $id)
    {
        return $this->conversations()->with("users")->where("id", $id)->first();
    }

    public function getPost(?int $post_id)
    {
        return $this->posts()->find($post_id);
    }

    public function createPost(string $content, ?string $url)
    {
        return $this->posts()->create([
            "content" => $content,
            "image_url" => $url
        ]);
    }

    public function updatePost(Post $post, string $content, ?string $url, ?int $delete_image)
    {
        if ($delete_image) {
            $url = null;
        } else {
            if (!$url) {
                $url = $post->url;
            }
        }
        $post->update([
            "content" => $content,
            "image_url" => $url
        ]);

        return $post->refresh();
    }

    public function toggleLike(Post $post)
    {
        $like = $post->likes()->where("user_id", $this->id)->first();
        if ($like) {
            $like->delete();
        } else {
            $like = $this->likes()->save($post->likes()->make([]));
        }

        return;
    }
}
