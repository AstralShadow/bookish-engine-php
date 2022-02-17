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
use \Model\User as MUser;
use \Model\Session as MSession;


class User
{

    #[POST]
    public static function createUser()
    {
        if (
            !isset($_POST["name"], $_POST["password"]) ||
            !is_string($_POST["name"]) ||
            !is_string($_POST["password"])
        ){
            $response = new ApiResponse(400);
            $response->echo([
                "error" => "Изпратете name и password за да създадете акаунт"
                // Send name and password to create account
            ]);
            return $response;
        }
        $name = trim($_POST["name"]);
        $pwd = trim($_POST["password"]);

        if (strlen($name) < 2){
            $response = new ApiResponse(400);
            $response->echo([
                "error" => "Моля попълнете име и парола"
                // Please, fill the name and password fields
            ]);
            return $response;
        }

        if (MUser::exists($name)){
            $response = new ApiResponse(409);
            $response->echo([
                "error" => "Потребителят вече съществува" // User already existing
            ]);
            return $response;
        }

        new MUser($name, $pwd);
        return new ApiResponse(200);
    }

    #[GET("/{name}")]
    public static function publicData(Request $req)
    {
        $name = $req->name;
        $user = MUser::find(["name" => $name]);
        if (count($user) == 0){
            $response = new ApiResponse(404);
            $response->echo([
                "error" => "Потребителят не беше намерен" // User not found
            ]);
            return $response;
        }

        unset($user->password);
        $response = new ApiResponse(200);
        $response->echo($user);
        return $response;
    }

    #[GET]
    public static function privateData()
    {
        $session = MSession::fromPOSTorCookie();
        if (!isset($session)){
            $response = new ApiResponse(403);
            $response->echo([
                "error" => "Достъпът отказан" // Access denied
            ]);
            return $response;
        }

        $user = $session->user;

        $response = new ApiResponse(200);
        $response->echo($user);
        return $response;
    }

    #[DELETE]
    public static function deleteUser()
    {
        $session = MSession::fromPOSTorCookie();
        if (!isset($session)){
            $response = new ApiResponse(403);
            $response->echo([
                "error" => "Достъпът отказан" // Access denied
            ]);
            return $response;
        }
        $user = $session->user;
        foreach ($user->getSessions() as $session){
            MSession::delete($session);
        }

        MUser::delete($user->getId());

        return new ApiResponse(200);
    }

    #[Fallback]
    public static function fallback()
    {
        $response = new ApiResponse(400);
        $response->echo([
            "error" => "Невалидна заявка" // Invalid request
        ]);
        return $response;
    }

}
