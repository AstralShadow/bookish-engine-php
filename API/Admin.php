<?php
namespace API;

use \Core\Request;
use \Core\Responses\ApiResponse;
use \Core\Responses\InstantResponse;
use Core\RequestMethods\GET;
use Core\RequestMethods\PUT;
use Core\RequestMethods\POST;
use Core\RequestMethods\DELETE;
use Core\RequestMethods\Fallback;
use Core\RequestMethods\StartUp;

use function Extend\APIError;
use function Extend\isValidString;
use function Extend\uploadImage;
use function Extend\shrinkAvatar;
use Extend\CSRFTokenManager as CSRF;

use \Model\Session;
use \Model\Resource;
use \Model\User;
use \Model\Role;
use \Model\Junction\UserRole;
use \Extend\Permissions;

class Admin
{

    #[GET("/users")]
    public static function listUsers()
    {
        $user = Session::current()?->User;
        $permission = Permissions::CanGiveRoles;
        if(!$user || !$user->has($permission))
            return APIError(403);

        $users = User::find([]);
        $data = [];
        foreach($users as $user)
        {
            $data[] = $user->overview();
        }

        $response = new ApiResponse(200);
        $response->echo($data);
        return $response;
    }

    #[POST("/give_mod")]
    public static function give_mod()
    {
        $user = Session::current()?->User;
        $permission = Permissions::CanGiveRoles;
        if(!$user || !$user->has($permission))
            return APIError(403);

        if(!CSRF::weak_check())
            return APIError(400, "Bad CSRF token.");

        $name =& $_POST["user"];
        if(!isValidString($name))
            return APIError(400, "Invalid name");

        $target = User::find([ "Name" => trim($name) ]);
        if(!count($target))
            return APIError(404, "User not found");

        $role = Role::find([ "Name" => "moderator" ]);
        $link = UserRole::get($target[0], $role[0]);
        if($link == null)
            new UserRole($target[0], $role[0], $user);

        return new ApiResponse(200);
    }

    #[POST("/take_mod")]
    public static function take_mod()
    {
        $user = Session::current()?->User;
        $permission = Permissions::CanGiveRoles;
        if(!$user || !$user->has($permission))
            return APIError(403);
        
        if(!CSRF::weak_check())
            return APIError(400, "Bad CSRF token.");

        $name =& $_POST["user"];
        if(!isValidString($name))
            return APIError(400, "Invalid name");

        $target = User::find([ "Name" => trim($name) ]);
        if(!count($target))
            return APIError(404, "User not found");

        $role = Role::find([ "Name" => "moderator" ]);
        $link = UserRole::get($target[0], $role[0]);
        if($link != null)
            $link->delete($link);

        return new ApiResponse(200);
    }

    #[GET("/new_resources")]
    public static function approvals()
    {
        $user = Session::current()?->User;
        $approve_perm = Permissions::CanApproveResources;
        if(!$user || !$user->has($approve_perm))
            return APIError(403);
        
        $resources = [];
        foreach(Resource::new_resources() as $r)
            $resources[] = $r->overview();
        $response = new ApiResponse(200);
        $response->echo($resources);
        return $response;
    }

    #[GET("/new_resources/{tags}")]
    public static function approvalsSearch(Request $req)
    {
        $user = Session::current()?->User;
        $approve_perm = Permissions::CanApproveResources;
        if(!$user || !$user->has($approve_perm))
            return APIError(403);

        $query = mb_strtolower($req->tags);
        $keywords = array_map(function($a){
            return urldecode($a);
        }, explode('+', $query));


        $extra = true;
        $tags =
            Search::keywordsToTags($keywords, $extra);

        $owned = Resource::new_resources();
        $resources =
            Search::findIn($tags, $owned, $extra);

        $data = array_map(function($res)
        {
            return $res->overview();
        }, $resources);
        
        $response = new ApiResponse(200);
        $response->setHeader
            ("cache-control", "no-cache");
        $response->echo($data);
        return $response;
    }

    #[Fallback]
    public static function fallback()
    {
        return APIError(400, "Invalid request");
    }

}
