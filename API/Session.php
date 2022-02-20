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

use function Extend\APIError;
use function Extend\isValidString;
use Extend\CSRFTokenManager as CSRF;

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
        $session = MSession::current();
        if(!isset($session))
            return APIError(404, "Invalid session");

        $response = new ApiResponse(200);
        $response->echo([
            "user" => $session->user->Name,
            "login_time" => $session->Created,
            "token" => $session->token()
        ]);
        return $response;
    }

    #[POST]
    public static function login()
    {
        if(!isValidString($_POST["name"], 3) ||
           !isValidString($_POST["password"], 6))
        {
            return APIError(400, "Непопълнено име" .
                                 "или парола.");
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

        return APIError(403, "Грешно име или парола");
    }

    #[DELETE]
    public static function logout()
    {
        $session = MSession::current();

        if (!isset($session))
            return APIError(404, "Session not found");

        if(!CSRF::weak_check())
            return APIError(400, "Invalid CSRF token");
        
        MSession::delete($session->getId());

        return new ApiResponse(200);
    }

    #[Fallback]
    public static function fallback()
    {
        return APIError(400, "Invalid request");
    }


}
