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

use \Model\Tag;
use \Model\Resource;


class Filter
{

    const RESPECT_RESOURCE_TAG_COUNT = true;

    #[GET("/{tags}")]
    public static function find(Request $req)
    {
        $query = mb_strtolower($req->tags);
        $query_tags = array_map(function($a){
            return urldecode($a);
        }, explode('+', $query));
        $all_tags = Tag::find([]);

        $tags = [];

        foreach($all_tags as $tag)
        {
            if(in_array($tag->Name, $query_tags))
                $tags[] = $tag;
        }

        if(!count($tags))
        {
            $response = new ApiResponse(200);
            $response->echo([]);
            return $response;
        }

        $resources = [];
        foreach($tags as $tag)
        foreach($tag->Resources() as $link)
        {
            if(!isset($link->ApproveTime))
                continue;

            $resource = $link->Resource;
            $id = $resource->getId();

            if(!isset($resources[$id]))
            {
                $tag_count =
                    self::RESPECT_RESOURCE_TAG_COUNT ?
                    count($resource->Tags()) : 1;
                $resources[$id] = [
                    "id" => $id,
                    "tags" => 0,
                    "all_tags" => $tag_count
                ];
            }
            $resources[$id]["tags"]++;
        }

        foreach($resources as $key => $val)
        {
            $tag_rate = $val["tags"] / $val["all_tags"];
            $resources[$key]["rate"] = $tag_rate;
        }

        usort($resources, function($a, $b){
            return $b["rate"] <=> $a["rate"];
        });


        $limit = 100;
        $max = $resources[0]["rate"];
        $min = $max / 8;
        $answer = [];

        foreach($resources as $val)
        {
            if(count($answer) >= $limit)
                break;

            $resource = Resource::get($val["id"]);
            
            if($val["rate"] < $min)
                break;
            $data = $resource->overview();
            $data["likeliness"] = $val["rate"];
            $answer[]  = $data;
        }

        $response = new ApiResponse(200);
        $response->setHeader("cache-control", "no-cache");
        $response->echo($answer);
        return $response;
    }

    #[Fallback]
    public static function fallback()
    {
        return APIError(400, "Invalid request");
    }

}
