<?php

namespace App\Http\Middleware;

use App\User;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CheckAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if($this->isAdmin($request))
        {
            return $next($request);
        }

        $response = new JsonResponse([
            "message" => "Unauthorized"
        ]);

        $response->setStatusCode(401, "Unauthorized");

        return $response;
    }

    public static function isAdmin(Request $request): bool {
        $token = $request->bearerToken();

        if($token){
            $admin = User::where("token", "=", $token);

            if($admin){
                return true;
            }
        }

        return false;
    }
}