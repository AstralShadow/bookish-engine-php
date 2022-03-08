<?php
namespace Controllers;

use Core\Request;
use Core\RequestMethods\GET;
use Core\RequestMethods\PUT;
use Core\RequestMethods\POST;
use Core\RequestMethods\DELETE;
use Core\RequestMethods\Fallback;
use Core\RequestMethods\StartUp;
use Core\RequestMethods\RequestMethod;

use function Extend\layoutResponseFactory as Page;
use function Extend\redirect;
use Extend\CSRFTokenManager as CSRF;

use Model\Session;
use Model\User as MUser;


class User
{

    #[GET]
    #[POST]
    public static function index($r)
    {
        $user = Session::current()?->User;
        if(!isset($user))
            return redirect("./login?next=user");
            
        $response = Page("user.html", 200);
        $response->setValue("csrf", CSRF::get());

        if($r->method() == RequestMethod::POST)
        {
            $state = \API\Resource::create();
            $code = $state->getCode();
            $data = $state->getOutput();
            if($code == 201)
            {
                $uri = "/resource/" . $data["id"];
                return redirect($uri);
            }
            else
            {
                $msg = $data["error"] ?? $code;
                $response->setValue("error", $msg);
            }
        }

        return $response;
    }

    #[GET("/resources")]
    public static function resources()
    {
        $user = Session::current()?->User;
        if(!$user)
            return redirect("/login?next=/accured");

        $response = Page("search.html", 200);
        $response->setValue("sourceName",
            "Твоите материали");
        $response->setValue("source", json_encode(
            "/api/user/" .
            urlencode($user->Name) .
            "/resources/"));

        return $response;
    }

    #[GET("/accured")]
    public static function accured()
    {
        $user = Session::current()?->User;
        if(!$user)
            return redirect("/login?next=/accured");

        $response = Page("search.html", 200);
        $response->setValue("sourceName",
            "Закупени материали");
        $response->setValue("source", json_encode(
            "/api/user/" .
            urlencode($user->Name) .
            "/accured/"));

        return $response;
    }

}
