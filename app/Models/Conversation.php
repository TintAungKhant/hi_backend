<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
    use HasFactory;

    protected $fillable = ["updated_at"];

    public function users()
    {
        return $this->belongsToMany(User::class);
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    public function latest_message()
    {
        return $this->hasOne(Message::class)->with("messageable")->latest();
    }

    public function getMessages(?Message $last_message)
    {
        return $this->load(["messages" => function ($q) use ($last_message) {
            if ($last_message) {
                $q->where("messages.id", "<>", $last_message->id);
                $q->where("created_at", "<=", $last_message->created_at);
            }
            $q->with("messageable")->limit(20)->orderBy("created_at", "DESC");
        }])->makeHidden("pivot");
    }
}
