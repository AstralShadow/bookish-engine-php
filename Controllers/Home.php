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


class Home
{

    #[GET]
    public static function index()
    {
        return redirect("/login?next=/user");
        $response = Page("index.html", 501);

        return $response;
    }

    #[Fallback]
    public static function notFound()
    {
        return Page("404.html");
    }

}
