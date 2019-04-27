<?php

namespace App\Http\Controllers;

use App\Comment;
use App\Http\Middleware\CheckAdmin;
use App\Advert;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Validator;

class AdvertsController extends Controller
{
    const DateFormat = "H:i d.m.Y";

    /**
     * Display a listing of the resource.
     *
     * @return Advert[]|\Illuminate\Database\Eloquent\Collection
     */
    public function index()
    {
        $adverts = Advert::all();

        foreach ($adverts as $advert){
            $advert->tags = explode(',',$advert->tags);
        }

        return $this->jsonResponse($adverts, 200, "List adverts");
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

        $newAdvert = new Advert();

        if (!$this->createOrUpdateAdvert($request, $newAdvert))
        {
            return response("Error while saving data in db", 500);
        }

        return $this->jsonResponse(["status" => true, "advert_id" => $newAdvert->id],
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
        $advert = Advert::with('comments')->find($id);

        if (!$advert)
        {
            return $this->jsonResponse([
                "message" => "Advert not found"
            ], 404, "Advert not found");
        }

        $advert->tags = explode(',',$advert->tags);

        return $this->jsonResponse($advert, 200, "View advert");
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
        $advert = Advert::find($id);

        if (!$advert)
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

        if (!$this->createOrUpdateAdvert($request, $advert))
            return response("Error while trying to save data in db", 500);


        $advert->tags = explode(',',$advert->tags);

        return $this->jsonResponse(["status" => true, "advert" => $advert], 201, "Successful creation");
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $advert = Advert::find($id);

        if (!$advert)
        {

            return $this->jsonResponse(["message" => "Advert not found"],
                404, "Advert not found");

        }

        if (!$advert->delete())
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

        $advert = Advert::find($id);

        if (!$advert)
        {

            return $this->jsonResponse(["message" => "Advert not found"],
                404, "Advert not found");

        }

        if ($validator->fails())
        {
            $response["status"] = false;
            $response["message"] = $validator->errors();

            return $this->jsonResponse($response, 400, 'Creating error');
        }

        $newComment = new Comment();

        $newComment->advert_id = $id;
        $newComment->author = $isAdmin ? 'admin' : $request->author;
        $newComment->comment = $request->comment;
        $newComment->datatime = date(self::DateFormat);

        $newComment->save();

        return $this->jsonResponse(["status" => true], 201, 'Successful creation');
    }

    public function removeComment($advertId, $commentId)
    {
        $advert = Advert::find($advertId);

        if (!$advert)
        {
            return $this->jsonResponse(["message" => "Advert not found"], 404, "Advert not found");
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
        $adverts = Advert::where("tags", "like", '%' . $tagName . '%')->get();

        foreach ($adverts as $advert){
            $advert->tags = explode(',',$advert->tags);
        }

        return $this->jsonResponse(["body" => $adverts], 200, "Found adverts");
    }

    /**
     * @param Request $request
     * @param $newAdvert
     * @return bool
     */
    private function createOrUpdateAdvert(Request $request, $newAdvert): bool
    {
        //I would consider using somekind of "mapper" in real code
        $newAdvert->title = $request->title;
        $newAdvert->number = $request->number;

        $newAdvert->image = $this->saveImageAndGetPath($request);

        $newAdvert->text = $request->text;

        $newAdvert->datatime = date(self::DateFormat);

        if ($request->has('tags'))
        {
            $newAdvert->tags = $request->tags;
        }

        return $newAdvert->save();
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
            'title' => 'required|unique:adverts',
            'number' => 'required',
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
        $imagePath = Storage::disk('advert_images')->put('', $request->image);

        return "advert_images/" . $imagePath;
    }

}
