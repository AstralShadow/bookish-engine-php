<?php
namespace Controllers;

use Core\Request;
use function Extend\layoutResponseFactory as Page;
use function Extend\redirect;
use Core\RequestMethods\GET;
use Core\RequestMethods\PUT;
use Core\RequestMethods\POST;
use Core\RequestMethods\DELETE;
use Core\RequestMethods\Fallback;
use Core\RequestMethods\StartUp;

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
            self::$user = self::$session->user;
    }

    #[GET("/user")]
    public static function index()
    {
        if(!isset(self::$user))
            return redirect();
            
        $response = Page("user.html", 200);

        return $response;
    }

}
