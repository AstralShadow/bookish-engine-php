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

    public static ?MUser $user;
    public static ?Session $session;

    #[StartUp]
    public static function load_user()
    {
        self::$session = Session::fromCookie();
        if(isset(self::$session))
            self::$user = self::$session->User;
    }

    #[GET("/user")]
    #[POST("/user")]
    public static function index($r)
    {
        if(!isset(self::$user))
            return redirect("./login?next=user");
            
        $response = Page("user.html", 200);
        $response->setValue("csrf", CSRF::get());
        $response->setValue("name", self::$user->Name);

        if($r->method() == RequestMethod::POST)
        {
            $state = \API\Resource::create();
            $code = $state->getCode();
            $data = $state->getOutput();
            if($code >= 400)
            {
                $response->setValue("error",
                                $data["error"] ?? $code);
            }
            if($code == 201)
            {
                $uri = "/resource/" . $data["id"];
                $response->setValue("error", $uri);
            }
        }

        return $response;
    }

}
