<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

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

        return response()->json($posts);
    }

    public function show(Post $post)
    {
        if ($post->is_draft || $post->published_at > now()) {
            abort(404);
        }

        return response()->json($post);
    }

    public function create()
    {
        return 'post.create';
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required',
            'is_draft' => 'required|boolean',
            'published_at' => 'nullable|date',
        ]);

        $post = auth()->user()->posts()->create($validated);

        return response()->json($post, 201);
    }

    public function edit(Post $post)
    {
        $this->authorize('update', $post);

        return 'posts.edit';
    }

    public function update(Request $request, Post $post)
    {
        $this->authorize('update', $post);

        $validated = $request->validate([
            'title' => 'string|max:255',
            'content' => 'string',
            'is_draft' => 'boolean',
            'published_at' => 'nullable|date',
        ]);

        $post->update($validated);

        return response()->json($post);
    }

    public function destroy(Post $post)
    {
        $this->authorize('delete', $post);

        $post->delete();

        return response()->json(['message' => 'Post deleted successfully']);
    }
}
