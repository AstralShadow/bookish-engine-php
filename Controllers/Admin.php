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


class Admin
{

    #[GET("/admin")]
    public static function admin()
    {
        $response = Page("admin.html", 501);

        return $response;
    }

}
