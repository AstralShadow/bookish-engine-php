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

use \Model\User;
use \Model\Session;
use \Model\Tag;


class Tags
{

    #[POST]
    public static function createTag()
    {
        $user = Session::current()?->User;
        if(!$user) return APIError(401);

        if(!CSRF::weak_check())
            return APIError(400, "Bad CSRF token.");

        if(!isValidString($_POST["name"], 3))
            return APIError(400, "Invalid name");

        $forbidden = [
            ',', '.',
            '+', ';',
            '/', "\\",
            "\n"
        ];
        foreach($forbidden as $char)
            if(strpos($_POST["name"], $char) !== false)
                return APIError(400, "Invalid name");

        $name = trim($_POST["name"]);
        $info = isValidString($_POST["info"], 10) ?
                    trim($_POST["info"]) : null;
        
        if(Tag::exists($name))
            return APIError(409, "Already existing.");

        $tag = new Tag($user, $name, $info);
        return new ApiResponse(200);
    }

    #[GET]
    public static function listTags()
    {
        $data = [];
        $tags = Tag::find([]);
        foreach($tags as $tag)
            $data[] = $tag->data();

        $response = new ApiResponse(200);
        $response->echo($data);
        return $response;
    }

    #[Fallback]
    public static function fallback()
    {
        return APIError(400, "Invalid request");
    }

}
