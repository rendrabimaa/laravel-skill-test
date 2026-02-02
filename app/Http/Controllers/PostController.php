<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePostRequest;
use App\Http\Requests\UpdatePostRequest;
use App\Http\Resources\PostResource;
use App\Models\Post;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class PostController extends Controller
{
    use AuthorizesRequests;

    public function index()
    {
        $posts = Post::with('user')
            ->where('is_draft', false)
            ->where('published_at', '<=', now())
            ->latest()
            ->paginate(20);

        return PostResource::collection($posts);
    }

    public function show(Post $post)
    {
        if ($post->is_draft || $post->published_at > now()) {
            abort(404);
        }

        return new PostResource($post->load('user'));
    }

    public function create()
    {
        return 'post.create';
    }

    public function store(StorePostRequest $request)
    {

        $post = auth()->user()->posts()->create($request->validated());

        return new PostResource($post);
    }

    public function edit(Post $post)
    {
        $this->authorize('update', $post);

        return 'posts.edit';
    }

    public function update(UpdatePostRequest $request, Post $post)
    {
        $this->authorize('update', $post);

        $post->update($request->validated());

        return new PostResource($post);
    }

    public function destroy(Post $post)
    {
        $this->authorize('delete', $post);

        $post->delete();

        return response()->json(['message' => 'Post deleted successfully']);
    }
}
