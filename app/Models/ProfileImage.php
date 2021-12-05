<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProfileImage extends Model
{
    use HasFactory;

    protected $fillable = [
        "url",
        "type"
    ];

    const PROFILE_IMAGE_TYPES = [
        "profile" => 1,
        "featured" => 2
    ];

    public function user(){
        return $this->belongsTo(User::class);
    }
}
