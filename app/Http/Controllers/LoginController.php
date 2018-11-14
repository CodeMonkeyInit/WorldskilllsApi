<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    public function login(Request $request)
    {
        $validator = $this->createValidator($request);

        if (!$validator->fails())
        {
            $user = User::where("login",  "=", $request->login)
                ->where("password", "=", $request->password)->first();

            if(!$user)
            {
                return response()
                    ->setStatusCode(401, "Invalid authorization data")
                    ->json([
                        "status" => false,
                        "message" => "Invalid authorization data"
                    ]);
            }

            return response()->setStatusCode(200, "Successful authorization")
                ->json([
                    "status" => true,
                    "token" => $user->token
                ]);
        }

        return response()
            ->setStatusCode(401, "Invalid authorization data")
            ->json([
                "status" => false,
                "message" => "Invalid authorization data"
            ]);

    }

    /**
     * @param Request $request
     * @return mixed
     */
    private function createValidator(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'login' => 'required',
            'password' => 'required',
        ]);
        return $validator;
    }
}