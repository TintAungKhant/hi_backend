<?php

namespace App\Http\Controllers\Api\V1;

use App\Exceptions\Api\V1\InternalErrorException;
use App\Http\Requests\Api\V1\GetPostsRequest;
use App\Http\Requests\Api\V1\StorePostRequest;
use App\Http\Requests\Api\V1\ToggleLikeRequest;
use App\Http\Requests\Api\V1\UpdatePostRequest;
use App\Models\Post;
use App\Services\FileUpload;
use Exception;
use App\Traits\ApiResponseTrait;

class PostController extends BaseController
{
    use ApiResponseTrait;

    public function get(GetPostsRequest $request)
    {
        try {
            $posts = Post::getPosts($request->get("page", 1), $this->auth_user);

            return $this->successResponse([
                "posts" => $posts
            ]);
        } catch (Exception $e) {
            throw new InternalErrorException($e);
        }
    }

    private function uploadImage($request)
    {
        $file_path = null;

        if ($request->hasFile("image")) {
            $fileUpload = new FileUpload;
            $file_path = $fileUpload->save($request->file("image"), "image");
        }

        return $file_path;
    }

    public function store(StorePostRequest $request)
    {
        try {
            $post = $this->auth_user->createPost($request->get("content"), $this->uploadImage($request));

            return $this->successResponse([
                "post" => $post
            ]);
        } catch (Exception $e) {
            throw new InternalErrorException($e);
        }
    }

    public function update(UpdatePostRequest $request, $post_id)
    {
        try {
            $post = $this->auth_user->getPost($post_id);

            if (!$post) {
                return $this->failResponse([
                    "message" => "Post not found"
                ], 404);
            }

            $post = $this->auth_user->updatePost(
                $post,
                $request->get("content"),
                $this->uploadImage($request),
                $request->get("delete_image")
            );

            return $this->successResponse([
                "post" => $post
            ]);
        } catch (Exception $e) {
            throw new InternalErrorException($e);
        }
    }

    public function toggleLike($post_id)
    {
        try {
            $post = Post::find($post_id);

            if (!$post) {
                return $this->failResponse([
                    "message" => "Post not found"
                ], 404);
            }

            $this->auth_user->toggleLike($post);

            return $this->successResponse([]);
        } catch (Exception $e) {
            throw new InternalErrorException($e);
        }
    }
}
