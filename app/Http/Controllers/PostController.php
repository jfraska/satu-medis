<?php

namespace App\Http\Controllers;

use App\Http\Helper;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PostController extends Controller
{
    use Helper;

    public function index(Request $request)
    {
        $title = $request->input('title');

        return $this->responseFormatterWithMeta(
            $this->httpCode['StatusOK'],
            $this->httpMessage['StatusOK'],
            Post::when($title, function ($query, $title) {
                return $query->where('title', 'like', "%{$title}%");
            })->orderBy('created_at', 'desc')
            ->cursorPaginate($request->input('per_page', 5)));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string',
            'content' => 'nullable|string',
            'is_published' => 'nullable|boolean',
            'meta_description' => 'nullable|string',

        ]);

        if ($validator->fails()) {
            return $this->errorResponseFormatter($this->httpCode['StatusUnprocessableEntity'], $this->httpMessage['StatusUnprocessableEntity'], $validator->errors());
        }

        $post = Post::create([
            'title' => $request->title,
            'slug' => $request->slug,
            'content' => $request->content,
            'is_published' => $request->is_published,
            'meta_description' => $request->meta_description,
            'user_id' => auth()->user()->id,
        ]);

        return $this->responseFormatter($this->httpCode['StatusCreated'], $this->httpMessage['StatusCreated'], $post);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'nullable|string',
            'content' => 'nullable|string',
            'is_published' => 'nullable|boolean',
            'meta_description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return $this->errorResponseFormatter($this->httpCode['StatusUnprocessableEntity'], $this->httpMessage['StatusUnprocessableEntity'], $validator->errors());
        }

        $post = $this->getData($id);

        if ($post == null) return $this->errorResponseFormatter($this->httpCode['StatusUnprocessableEntity'], "Data Not Found");

        $post->title = $request->title;
        $post->content = $request->content;
        $post->is_published = $request->is_published;
        $post->meta_description = $request->meta_description;
        $post->save();

        return $this->responseFormatter($this->httpCode['StatusOK'], $this->httpMessage['StatusOK'], $post);
    }

    public function destroy($id)
    {
        $post = $this->getData($id);

        if ($post == null) return $this->errorResponseFormatter($this->httpCode['StatusUnprocessableEntity'], "Data Not Found");

        $post->delete();

        return $this->responseFormatter($this->httpCode['StatusOK'], $this->httpMessage['StatusOK'], ["deleted_at" => $post->deleted_at]);
    }

    public function show($id)
    {
        $post = $this->getData($id);

        if ($post == null) return $this->errorResponseFormatter($this->httpCode['StatusUnprocessableEntity'], "Data Not Found");

        return $this->responseFormatter($this->httpCode['StatusOK'], $this->httpMessage['StatusOK'], $post);
    }


    protected function getData($id)
    {
        return Post::find($id);
    }
}
