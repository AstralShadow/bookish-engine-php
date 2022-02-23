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

use \Model\User;
use \Model\Session;
use \Model\Tag;
use \Model\Resource as MResource;
use \Model\Junction\ResourceTag;


class Resource
{

    #[POST]
    public static function create()
    {
        if(!CSRF::weak_check())
            return APIError(400, "Невалидна сесия.");

        $user = self::getUser();
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

    private static function getUser() : ?User
    {
        return Session::current()?->User;
    }

    private static
    function applyModifications(MResource &$resource)
    {
        $response = new ApiResponse(200);
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


        $file = uploadFile("full");
        if($file != null)
        {
            $resource->Data = $file["uri"];
            $resource->DataName = $file["name"];
            $resource->DataSize = $file["size"];
            $resource->DataMime = $file["mime"];
            $modified = true;
        }

        $demo = uploadFile("demo");
        if($demo != null)
        {
            $resource->Preview = $demo["uri"];
            $resource->PreviewName = $demo["name"];
            $resource->PreviewSize = $demo["size"];
            $resource->PreviewMime = $demo["mime"];
            $modified = true;
        }

        if($modified)
            $resource->save();


        $tags = &$_POST["tags"];
        $user = self::getUser();
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
        $response->echo([
            "id" => $resource->getId()
        ]);
        return $response;
    }

    #[PUT("/{id}")]
    public static function modify(Request $req)
    {
        if(!CSRF::weak_check())
            return APIError(400, "Невалидна сесия.");

        $user = self::getUser();
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

    #[GET("/{id}")]
    public static function overview(Request $req)
    {
        $id = $req->id;
        $res = MResource::get($id);
        if(!$res)
            return APIError(404, "Няма такъв ресурс.");

        $response = new ApiResponse(200);
        $response->echo($res->overview());
        return $response;
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

        $response = new InstantResponse(200);
        $response->setHeader("content-type",
                             $res->PreviewMime);
        readfile($uri);
        return $response;
    }

    #[DELETE("/{id}")]
    public static function delete($req)
    {
        if(!CSRF::weak_check())
            return APIError(400, "Невалидна сесия.");

        $user = self::getUser();
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

    #[Fallback]
    public static function fallback()
    {
        return APIError(400, "Невалидна заявка");
    }

}
