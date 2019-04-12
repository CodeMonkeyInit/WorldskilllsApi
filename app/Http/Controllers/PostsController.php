<?php

namespace App\Http\Controllers;

use App\Comment;
use App\Http\Middleware\CheckAdmin;
use App\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Validator;

class PostsController extends Controller
{
    const DateFormat = "H:i d.m.Y";

    /**
     * Display a listing of the resource.
     *
     * @return Post[]|\Illuminate\Database\Eloquent\Collection
     */
    public function index()
    {
        $posts = Post::all();

        foreach ($posts as $post){
            $post->tags = explode(',',$post->tags);
        }

        return $this->jsonResponse($posts, 200, "List posts");
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $response = [];

        $validator = $this->createValidator($request);

        if ($validator->fails())
        {
            $response["status"] = false;
            $response["message"] = $validator->errors();

            return $this->jsonResponse($response, 400, "Creating error");
        }

        $newPost = new Post();

        if (!$this->createOrUpdatePost($request, $newPost))
        {
            return response("Error while saving data in db", 500);
        }

        return $this->jsonResponse(["status" => true, "post_id" => $newPost->id],
            201, "Successful creation");

    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $post = Post::with('comments')->find($id);

        if (!$post)
        {
            return $this->jsonResponse([
                "message" => "Post not found"
            ], 404, "Post not found");
        }

        $post->tags = explode(',',$post->tags);

        return $this->jsonResponse($post, 200, "View post");
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $post = Post::find($id);

        if (!$post)
        {
            return $this->jsonResponse(["message" => "Advert not found"], 404, "Advert not found");
        }

        $validator = $this->createValidator($request);

        if ($validator->fails())
        {
            $response["status"] = false;
            $response["message"] = $validator->errors();

            return $this->jsonResponse($response, 400, "Editing error");
        }

        if (!$this->createOrUpdatePost($request, $post))
            return response("Error while trying to save data in db", 500);


        $post->tags = explode(',',$post->tags);

        return $this->jsonResponse(["status" => true, "post" => $post], 201, "Successful creation");
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $post = Post::find($id);

        if (!$post)
        {

            return $this->jsonResponse(["message" => "Post not found"],
                404, "Post not found");

        }

        if (!$post->delete())
        {
            return response("Something went terrebly wrong", 500);
        }


        return $this->jsonResponse(["status" => true], 201, "Successful delete");
    }


    public function addComment($id, Request $request)
    {
        $isAdmin = CheckAdmin::isAdmin($request);

        $validator = Validator::make($request->all(), [
            'author' => !$isAdmin ? 'required' : '',
            'comment' => 'required|max:255'
        ]);

        $response = [];

        $post = Post::find($id);

        if (!$post)
        {

            return $this->jsonResponse(["message" => "Post not found"],
                404, "Post not found");

        }

        if ($validator->fails())
        {
            $response["status"] = false;
            $response["message"] = $validator->errors();

            return $this->jsonResponse($response, 400, 'Creating error');
        }

        $newComment = new Comment();

        $newComment->post_id = $id;
        $newComment->author = $isAdmin ? 'admin' : $request->author;
        $newComment->comment = $request->comment;
        $newComment->datatime = date(self::DateFormat);

        $newComment->save();

        return $this->jsonResponse(["status" => true], 201, 'Successful creation');
    }

    public function removeComment($postId, $commentId)
    {
        $post = Post::find($postId);

        if (!$post)
        {
            return $this->jsonResponse(["message" => "Post not found"], 404, "Post not found");
        }

        $comment = Comment::find($commentId);

        if (!$comment)
        {
            return $this->jsonResponse(["message" => "Comment not found"], 404, "Comment not found");
        }

        $comment->delete();

        return $this->jsonResponse(["status" => true], 201, 'Successful delete');
    }

    public function searchByTag($tagName)
    {
        $posts = Post::where("tags", "like", '%' . $tagName . '%')->get();

        foreach ($posts as $post){
            $post->tags = explode(',',$post->tags);
        }

        return $this->jsonResponse(["body" => $posts], 200, "Found posts");
    }

    /**
     * @param Request $request
     * @param $newPost
     * @return bool
     */
    private function createOrUpdatePost(Request $request, $newPost): bool
    {
        //I would consider using somekind of "mapper" in real code
        $newPost->title = $request->title;
        $newPost->anons = $request->anons;

        $newPost->image = $this->saveImageAndGetPath($request);

        $newPost->text = $request->text;

        $newPost->datatime = date(self::DateFormat);

        if ($request->has('tags'))
        {
            $newPost->tags = $request->tags;
        }

        return $newPost->save();
    }

    /**
     * @param Request $request
     * @return mixed
     */
    private function createValidator(Request $request)
    {
        $bytesInMegabyte = 1000000;
        $fileLimit = 2 * $bytesInMegabyte;

        $validator = Validator::make($request->all(), [
            'title' => 'required|unique:posts',
            'anons' => 'required',
            'text' => 'required',
            'image' => "required|mimes:jpeg,png|max:{$fileLimit}",
        ]);
        return $validator;
    }

    /**
     * @param Request $request
     * @return string
     */
    private function saveImageAndGetPath(Request $request): string
    {
        $imagePath = Storage::disk('post_images')->put('', $request->image);

        return "post_images/" . $imagePath;
    }

}
