<?php
namespace Controllers;

use Core\Request;
use function Extend\layoutResponseFactory as Page;
use Core\RequestMethods\GET;
use Core\RequestMethods\PUT;
use Core\RequestMethods\POST;
use Core\RequestMethods\DELETE;
use Core\RequestMethods\Fallback;
use Core\RequestMethods\StartUp;

use function Extend\redirect;

use Model\Session;


class Search
{

    #[GET]
    public static function index()
    {
        $user = Session::current()?->User;
        if(!$user)
            return redirect("/login?next=/search");

        $response = Page("search.html", 200);

        return $response;
    }

}
