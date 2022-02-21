<?php
namespace API;

use \Core\Request;
use \Core\Responses\ApiResponse;
use Core\RequestMethods\GET;
use Core\RequestMethods\PUT;
use Core\RequestMethods\POST;
use Core\RequestMethods\DELETE;
use Core\RequestMethods\Fallback;
use Core\RequestMethods\StartUp;

use function Extend\APIError;
use function Extend\isValidString;
use Extend\CSRFTokenManager as CSRF;

use \Model\User as MUser;
use \Model\Session as MSession;


class User
{

    #[POST]
    public static function createUser()
    {
        if(!isValidString($_POST["name"], 3) ||
           !isValidString($_POST["password"], 6))
        {
            return APIError(400, "Въведете име+парола");
        }

        $name = trim($_POST["name"]);
        $pwd = trim($_POST["password"]);

        if (MUser::exists($name))
            return APIError(409,
                "Потребителят вече съществува");

        new MUser($name, $pwd);

        return new ApiResponse(200);
    }

    #[GET("/{name}")]
    public static function publicData(Request $req)
    {
        $name = $req->name;
        $users = MUser::find(["name" => $name]);
        if (count($users) == 0)
            return APIError(404, "Няма такъв профил");

        $user = $users[0];
        $response = new ApiResponse(200);
        $response->echo($user->overwiev());
        return $response;
    }

    #[GET]
    public static function privateData()
    {
        $session = MSession::current();
        if(!isset($session))
            return APIError(401, "Required to login");

        $user = $session->User;

        $response = new ApiResponse(200);
        $response->echo($user->privateOverwiev());
        return $response;
    }

    #[DELETE]
    public static function deleteUser()
    {
        $session = MSession::current();
        if (!isset($session))
            return APIError(401, "Required to login");

        $user = $session->User;
        foreach ($user->getSessions() as $session)
            MSession::delete($session);
        
        if(!CSRF::weak_check())
            return APIError(400, "Bad CSRF token.");

        MUser::delete($user->getId());

        return new ApiResponse(200);
    }

    #[Fallback]
    public static function fallback()
    {
        return APIError(400, "Invalid request");
    }

}
