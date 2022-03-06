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
use Extend\Permissions;

class Admin
{

    #[GET("/admin")]
    public static function admin()
    {
        $user = Session::current()?->User;
        $admin_perm = Permissions::CanGiveRoles;
        if(!$user || !$user->has($admin_perm))
            return Page("404.html", 404);

        $response = Page("admin.html", 200);
        

        return $response;
    }

    #[GET("/new_resources")]
    public static function approvals()
    {
        $user = Session::current()?->User;
        $approve_perm = Permissions::CanApproveResources;
        if(!$user || !$user->has($approve_perm))
            return Page("404.html", 404);

        $http = Page("search.html", 200);
        $http->setValue("sourceName", "Нови материали");
        $http->setValue("source", json_encode(
            "/api/admin/new_resources/"));
        return $http;
    }

}
