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
use \Extend\Permissions;

class Admin
{

    #[GET("/new_resources")]
    public static function approvals(Request $req)
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
        $user = Session::current()?->User;;
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
