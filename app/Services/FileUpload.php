<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class FileUpload
{
    private $file_name;
    private $user_id;

    public function __construct()
    {
        $this->file_name = \Carbon\Carbon::now()->timestamp . "_" . Str::random(30);
        $this->user_id = Auth::user()->id;
    }

    public function setFileName($value)
    {
        $this->file_name = $value;
        return $this;
    }

    public function setUserId($value)
    {
        $this->user_id = $value;
        return $this;
    }

    public function save($file, $file_type)
    {
        $file_extension = $file->extension();
        $full_file_name = $this->file_name . "." . $file_extension;
        if ($file_type == "image") {
            $file_path = Storage::disk("images")->putFileAs($this->user_id, $file, $full_file_name);
            $full_path = env("UPLOAD_PATH_IMAGES", "/uploads/images") . "/" . $file_path;
        } else if ($file_type == "video") {
            $file_path = Storage::disk("videos")->putFileAs($this->user_id, $file, $full_file_name);
            $full_path = env("UPLOAD_PATH_VIDEOS", "/uploads/videos") . "/" . $file_path;
        } else if ($file_type == "file") {
            $file_path = Storage::disk("files")->putFileAs($this->user_id, $file, $full_file_name);
            $full_path = env("UPLOAD_PATH_FILES", "/uploads/files") . "/" . $file_path;
        }

        return env("APP_URL").$full_path;
    }
}
