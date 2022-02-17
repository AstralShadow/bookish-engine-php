<?php
namespace API;

use \Core\Request;
use \Core\Responses\ApiResponse;
use \Core\RequestMethods\GET;
use \Core\RequestMethods\PUT;
use \Core\RequestMethods\POST;
use \Core\RequestMethods\DELETE;
use \Core\RequestMethods\FALLBACK;
use \Core\RequestMethods\StartUp;
use \Model\User as MUser;
use \Model\Session as MSession;

/**
 * Description of Home
 *
 * @author azcraft
 */
class Session
{
    #[GET]
    public static function getSessionData()
    {
        $session = MSession::fromPOSTorCookie();
        if (!isset($session)){
            $response = new ApiResponse(404);
            $response->echo([
                "error" => "Невалидна сесия" // Invalid session
            ]);
            return $response;
        }

        $response = new ApiResponse(200);
        $response->echo([
            "user" => $session->user->name,
            "loginTime" => $session->created,
            "token" => $session->token()
        ]);
        return $response;
    }

    #[POST]
    public static function login()
    {
        if (
            !isset($_POST["name"], $_POST["password"]) ||
            !is_string($_POST["name"]) ||
            !is_string($_POST["password"])
        ){
            $response = new ApiResponse(400);
            $response->echo([
                "error" => "Изпратете name и password за да влезете"
                // Send name and password to authenticate
            ]);
            return $response;
        }
        $name = trim($_POST["name"]);
        $pwd = trim($_POST["password"]);

        $user = MUser::login($name, $pwd);
        if ($user){
            $session = new MSession($user);
            $session->saveInCookie();

            $response = new ApiResponse(200);
            $response->echo([
                "token" => $session->token()
            ]);
            return $response;
        }

        $response = new ApiResponse(403);
        $response->echo([
            "error" => "Грешно име или парола" // Wrong name or password
        ]);
        return $response;
    }

    #[DELETE]
    public static function logout()
    {
        $session = MSession::fromPOSTorCookie();
        if (!isset($session)){
            $response = new ApiResponse(404);
            $response->echo([
                "error" => "Сесията не е намерена" // Session not found
            ]);
            return $response;
        }

        MSession::delete($session->getId());
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
