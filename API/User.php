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

use \Model\User as MUser;
use \Model\Session as MSession;


class User
{

    #[POST]
    public static function createUser()
    {
        if(!isValidString($_POST["name"], 3) ||
           !isValidString($_POST["password"], 6))
        {
            return APIError(400, "Въведете име+парола");
        }

        $name = trim($_POST["name"]);
        $pwd = trim($_POST["password"]);

        if (MUser::exists($name))
            return APIError(409,
                "Потребителят вече съществува");

        new MUser($name, $pwd);

        return new ApiResponse(200);
    }

    #[POST("/avatar")]
    public static function setAvatar(Request $req)
    {
        $user = MSession::current()?->User;
        if(!$user)
            return APIError(401);

        if(!CSRF::weak_check())
            return APIError(400, "Bad CSRF token");

        try
        {
            $img = uploadImage("avatar");
        }
        catch(Exception $e)
        {
            return APIError(500, $e->getMessage());
        }

        if(!$img)
            return APIError(400, "Missing avatar.");

        shrinkAvatar($img["uri"]);
        
        if(isset($user->Avatar) &&
           file_exists($user->Avatar) && 
           $user->Avatar != $img["uri"])
        {
            unlink($user->Avatar);
        }

        $user->Avatar = $img["uri"];
        $user->AvatarMime = $img["mime"];
        $user->save();

        $response = new ApiResponse(200);
        $response->echo(["uri" => $user->avatarUri()]);
        return $response;
    }

    #[GET("/{name}")]
    public static function publicData(Request $req)
    {
        $name = $req->name;
        $users = MUser::find(["name" => $name]);
        if (count($users) == 0)
            return APIError(404, "Няма такъв профил");

        $user = $users[0];
        $response = new ApiResponse(200);
        $response->echo($user->overview());
        return $response;
    }

    #[GET("/{name}/avatar")]
    public static function avatar(Request $req)
    {
        $name = $req->name;
        $users = MUser::find(["name" => $name]);
        if (count($users) == 0)
            return APIError(404);

        $user = $users[0];
        
        $uri = $user->Avatar ?? null;
        if(!isset($uri) || !file_exists($uri))
           return APIError(404);

        $response = new InstantResponse(200);
        $response->setHeader("content-type",
                             $user->AvatarMime);
        readfile($uri);
        return $response;
    }

    #[GET("/{name}/resources")]
    public static function creations(Request $req)
    {
        $name = $req->name;
        $users = MUser::find(["name" => $name]);
        if (count($users) == 0)
            return APIError(404);
        
        $user = $users[0];
        $resources = [];
        foreach($user->OwnedResources() as $r)
            $resources[] = $r->overview();
        $response = new ApiResponse(200);
        $response->echo($resources);
        return $response;
    }

    #[GET("/{name}/resources/{tags}")]
    public static function creationsSearch(Request $req)
    {
        $name = $req->name;
        $users = MUser::find(["name" => $name]);
        if (count($users) == 0)
            return APIError(404);
        
        $user = $users[0];
        $query = mb_strtolower($req->tags);
        $keywords = array_map(function($a){
            return urldecode($a);
        }, explode('+', $query));


        $extra = true;
        $tags =
            Search::keywordsToTags($keywords, $extra);

        $owned = $user->OwnedResources();
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

    #[GET("/{name}/accured")]
    public static function accured(Request $req)
    {
        $name = $req->name;
        $users = MUser::find(["name" => $name]);
        if (count($users) == 0)
            return APIError(404);
        
        $user = $users[0];
        $resources = [];
        foreach($user->AccuredResources() as $r)
            $resources[] = $r->Resource->overview();
        $response = new ApiResponse(200);
        $response->echo($resources);
        return $response;
    }

    #[GET("/{name}/accured/{tags}")]
    public static function accuredSearch(Request $req)
    {
        $name = $req->name;
        $users = MUser::find(["name" => $name]);
        if (count($users) == 0)
            return APIError(404);
        
        $user = $users[0];
        $query = mb_strtolower($req->tags);
        $keywords = array_map(function($a){
            return urldecode($a);
        }, explode('+', $query));


        $extra = true;
        $tags =
            Search::keywordsToTags($keywords, $extra);

        $accured = array_map(function($link) {
            return $link->Resource;
        }, $user->AccuredResources());
        $resources =
            Search::findIn($tags, $accured, $extra);

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


    #[GET]
    public static function privateData()
    {
        $session = MSession::current();
        if(!isset($session))
            return APIError(401, "Required to login");

        $user = $session->User;

        $response = new ApiResponse(200);
        $response->echo($user->privateOverview());
        return $response;
    }

    #[DELETE]
    public static function deleteUser()
    {
        $session = MSession::current();
        if (!isset($session))
            return APIError(401, "Required to login");

        $user = $session->User;
        foreach ($user->getSessions() as $session)
            MSession::delete($session);
        
        if(!CSRF::weak_check())
            return APIError(400, "Bad CSRF token.");

        MUser::delete($user->getId());

        return new ApiResponse(200);
    }

    #[Fallback]
    public static function fallback()
    {
        return APIError(400, "Invalid request");
    }

}
