<?php

namespace App\Http\Controllers;

use App\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Validator;

class PostsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Post[]|\Illuminate\Database\Eloquent\Collection
     */
    public function index()
    {
        return response()->setStatusCode(200, "List posts")->json(Post::all());
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

            return response()
                ->setStatusCode(400, "Creating error")
                ->json($response);
        }

        $newPost = new Post();

        if (!$this->createOrUpdatePost($request, $newPost))
        {
            return response("Error while saving data in db", 500);
        }

        return response()
            ->setStatusCode(201, "Successful creation")
            ->json(["status" => true, "post_id" => $newPost->id]);
    }

    public function addComment($id, Request $request)
    {

    }

    public function removeComment($postId, $commentId, Request $request)
    {

    }


    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $post = Post::find($id);

        if (!$post)
        {
            return response()->setStatusCode(404, "Post not found")->json([
                "message" => "Post not found"
            ]);
        }

        $post->comments = $post->comments->toArray();

        return $post;
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
            return response()->setStatusCode(404, "Not found");
        }

        $validator = $this->createValidator($request);

        if ($validator->fails())
        {
            $response["status"] = false;
            $response["message"] = $validator->errors();

            return response()
                ->setStatusCode(400, "Editing error")
                ->json($response);
        }

        if (!$this->createOrUpdatePost($request, $post))
            return response("Error while trying to save data in db", 500);

        return response()->setStatusCode(201, "Successful creation");
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
            return response()
                ->setStatusCode(404, "Post not found")
                ->json(["message" => "Post not found"]);
        }

        if (!$post->delete())
        {
            return response("Something went terrebly wrong", 500);
        }


        return response()->setStatusCode(201, "Successful delete")->json(["status" => true]);
    }

    /**
     * @param Request $request
     * @param $newPost
     * @return bool
     */
    private function createOrUpdatePost(Request $request, $newPost): bool
    {
        //todo validateTitle
        //I would consider using somekind of "mapper" in real code
        $newPost->title = $request->title;
        $newPost->anons = $request->anons;

        $imagePath = 'post_images/' . str_random(10) . $request->image->name;

        Storage::disk('local')->put($imagePath, $request->image);

        $newPost->image = $imagePath;

        $newPost->datatime = date("");

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
            'title' => 'required',
            'anons' => 'required',
            'text' => 'required',
            'image' => "required|mimes: jpg,png|size:{$fileLimit}",
        ]);
        return $validator;
    }

}
