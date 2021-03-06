<?php
namespace API;

use \Core\Request;
use \Core\Responses\InstantResponse;
use \Core\Responses\ApiResponse;
use Core\RequestMethods\GET;
use Core\RequestMethods\PUT;
use Core\RequestMethods\POST;
use Core\RequestMethods\DELETE;
use Core\RequestMethods\Fallback;
use Core\RequestMethods\StartUp;
use function Extend\APIError;
use function Extend\isValidString;
use function Extend\uploadFile;
use Extend\CSRFTokenManager as CSRF;
use Extend\Permissions;

use \Model\User;
use \Model\Session;
use \Model\Tag;
use \Model\Resource as MResource;
use \Model\ResourceFeedback;
use \Model\Junction\ResourceTag;
use \Model\Junction\UserResourceAccess;
use \Model\Junction\ResourceRating;


class Resource
{

    /* Owner section */

    #[POST]
    public static function create()
    {
        if(!CSRF::weak_check())
            return APIError(400, "Невалидна сесия.");

        $user = Session::current()?->User;
        if(!$user)
            return APIError(401, "Само регистрирани " .
                "потребители могат да създават ресурси");

        if(!isValidString($_POST["name"], 5))
            return APIError(400, "Въведете име (name).");

        $res = new MResource(trim($_POST["name"]),
                             $user);

        $state = self::applyModifications($res);
        $state->setCode(201);
        return $state;
    }

    private static
    function applyModifications(MResource &$resource)
    {
        $response = new ApiResponse(200);
        $answer = [
            "id" => $resource->getId()
        ];
        $modified = false;

        if(isValidString($_POST["name"]))
        {
            $name = trim($_POST["name"]);
            $resource->Name = $name;
            $modified = true;
        }

        if(isValidString($_POST["info"]))
        {
            $info = trim($_POST["info"]);
            $resource->Description = $info;
            $modified = true;
        }


        try {
            $file = uploadFile("full",
                               size_limit: 20000000);
            if($file != null)
            {
                $resource->Data = $file["uri"];
                $resource->DataName = $file["name"];
                $resource->DataSize = $file["size"];
                $resource->DataMime = $file["mime"];
                $modified = true;
            }
        } catch(Exception $e) {
            $answer["file_error"] = $e->getMessage();
        }

        try {
            $demo = uploadFile("demo",
                               size_limit: 20000000);
            if($demo != null)
            {
                $resource->Preview = $demo["uri"];
                $resource->PreviewName = $demo["name"];
                $resource->PreviewSize = $demo["size"];
                $resource->PreviewMime = $demo["mime"];
                $modified = true;
            }
        } catch(Exception $e) {
            $answer["demo_error"] = $e->getMessage();
        }

        if($modified)
            $resource->save();


        $tags = &$_POST["tags"];
        $user = Session::current()?->User;
        if(isset($tags) && is_array($tags))
        {
            foreach($tags as $tag_name)
            {
                if(isValidString($tag_name, 1))
                {
                    $tag = Tag::findByName($tag_name);
                    new ResourceTag($resource, $tag, $user);
                }
            }
        }


        $response = new ApiResponse(200);
        $response->echo($answer);
        return $response;
    }

    #[PUT("/{id}")]
    public static function modify(Request $req)
    {
        if(!CSRF::weak_check())
            return APIError(400, "Невалидна сесия.");

        $user = Session::current()?->User;
        if(!$user)
            return APIError(401, "Не сте в профила си.");

        $id = $req->id;
        $res = MResource::get($id);
        if(!$res)
            return APIError(404, "Няма такъв ресурс.");

        if($res->Owner->getId() != $user->getId())
            return APIError(403, "Не сте собственик.");

        return self::applyModifications($res);
    }

    #[DELETE("/{id}")]
    public static function delete($req)
    {
        if(!CSRF::weak_check())
            return APIError(400, "Невалидна сесия.");

        $user = Session::current()?->User;
        if(!$user)
            return APIError(401, "Не сте в профила си.");

        $id = $req->id;
        $res = MResource::get($id);
        if(!$res)
            return APIError(404, "Няма такъв ресурс.");

        if($res->Owner->getId() != $user->getId())
            return APIError(403, "Не сте собственик.");

        MResource::delete($res->getId());

        return new ApiResponse(200);
    }

    
    /* Moderator section */

    #[POST("/{id}/approve")]
    public static function approveResource(Request $req)
    {
        $id = $req->id;
        $res = MResource::get($id);
        if(!$res)
            return APIError(404, "Няма такъв ресурс.");

        if(!CSRF::weak_check())
            return APIError(400, "Bad CSRF token.");

        $permission = Permissions::CanApproveResources;
        $user = Session::current()?->User;
        if(!$user || !$user->has($permission))
            return APIError(403);

        $price = &$_POST["price"];
        if(isset($price) && is_numeric($price)
           && intval($price) > 0)
        {
            $res->Price = intval($price);
        }
        $res->Owner->Scrolls += $res->Price;
        $res->Owner->save();

        $note = &$_POST["note"];
        if(isValidString($note))
            $res->ApproveNote = $note;

        $res->approve($user);
        $http = new ApiResponse(200);
        $http->echo($res->overview());
        return $http;
    }


    /* Client section */

    #[GET("/{id}")]
    public static function overview(Request $req)
    {
        $id = $req->id;
        $res = MResource::get($id);
        if(!$res)
            return APIError(404, "Няма такъв ресурс.");

        $response = new ApiResponse(200);
        $data = $res->overview();
        
        $user = Session::current()?->User;
        if($user)
            $data["accured"] = self::isAccured($user, $res);
        
        $response->echo($data);
        return $response;
    }

    #[GET("/{id}/feedback")]
    public static function getFeedback(Request $req)
    {
        $id = $req->id;
        $res = MResource::get($id);
        if(!$res)
            return APIError(404, "Няма такъв ресурс.");

        $user = Session::current()?->User;

        $data = [];
        foreach($res->Feedback() as $feedback)
        {
            $overview = $feedback->overview();
            if($feedback->User->getId()
                == $user?->getId())
            {
                $overview["hide_name"] = true;
            }
            $data[] = $overview;
        }

        $http = new ApiResponse(200);
        $http->echo($data);
        return $http;
    }

    #[POST("/{id}/feedback")]
    public static function giveFeedback(Request $req)
    {
        $id = $req->id;
        $res = MResource::get($id);
        if(!$res)
            return APIError(404, "Няма такъв ресурс.");

        $user = Session::current()?->User;
        if(!$user)
            return APIError(401, "Влез в профила си");
        if(!CSRF::weak_check())
            return APIError(400, "Bad CSRF token");
        if(!self::isAccured($user, $res))
            return APIError(402, "Payment required");

        $msg = &$_POST["text"];
        if(!isValidString($msg, 10))
            return APIError(400, "Bad feedback message");

        $fb = new ResourceFeedback($res, $user, $msg);

        return new ApiResponse(200);
    }


    #[GET("/{id}/preview")]
    public static function downloadPreview(Request $req)
    {
        $id = $req->id;
        $res = MResource::get($id);
        if(!$res)
            return APIError(404, "Няма такъв ресурс.");

        $uri = $res->Preview ?? null;
        if(!isset($uri) || !file_exists($uri))
            return APIError(404);

        $name = addslashes($res->PreviewName);

        $response = new InstantResponse(200);
        $response->setHeader("content-type",
                             $res->PreviewMime);
        $response->setHeader("content-disposition",
            'attachment; filename="'.$name.'"');

        readfile($uri);
        return $response;
    }

    #[POST("/{id}/buy")]
    public static function buy(Request $req)
    {
        $id = $req->id;
        $res = MResource::get($id);
        if(!$res)
            return APIError(404, "Няма такъв ресурс");

        $uri = $res->Data ?? null;
        if(!isset($uri) || !file_exists($uri))
            return APIError(404, "Няма прикачен файл");

        $user = Session::current()?->User;
        if(!$user)
            return APIError
                (401, "Влез в профила си");
        if(!CSRF::weak_check())
            return APIError
                (400, "Invalid CSRF token.");
        if(self::isAccured($user, $res))
            return APIError
                (409, "Ресурса вече е закупен");
        
        if($user->Scrolls < $res->Price)
            return APIError
                (401, "Нямате достатъчно свитъци");

        new UserResourceAccess($user, $res, $res->Price);

        $res->Owner->Scrolls += $res->Price;
        $user->Scrolls -= $res->Price;

        $res->Owner->save();
        $user->save();

        return new ApiResponse(200);
    }

    public static function isAccured($user, $res)
    {
        $uid = $user->getId();
        $rid = $res->getId();
     
        $owned = $user == $res->Owner;
        $read = UserResourceAccess::get($uid, $rid);

        $permission = Permissions::CanApproveResources;
        $moderator = $user->has($permission);

        return (bool) $moderator || $read || $owned;
    }

    #[GET("/{id}/download")]
    public static function downloadData(Request $req)
    {
        $id = $req->id;
        $res = MResource::get($id);
        if(!$res)
            return APIError(404, "Няма такъв ресурс");

        $uri = $res->Data ?? null;
        if(!file_exists($uri))
            return APIError(404);

        $user = Session::current()?->User;
        if(!$user)
            return APIError
                (401, "Влез в профила си");

        if(!self::isAccured($user, $res))
            return APIError(402, "Изисква закупуване.");

        $name = addslashes($res->DataName);

        $response = new InstantResponse(200);
        $response->setHeader("content-type",
                             $res->DataMime);
        $response->setHeader("content-disposition",
            'attachment; filename="'.$name.'"');
        readfile($uri);
        return $response;
        
    }

    /* Misc */

    #[Fallback]
    public static function fallback()
    {
        return APIError(400, "Невалидна заявка");
    }

}
