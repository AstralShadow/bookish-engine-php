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


class Search
{

    #[GET("/{tags}")]
    public static function find(Request $req)
    {
        $query = mb_strtolower($req->tags);
        $tags = array_map(function($a){
            return urldecode($a);
        }, explode('+', $query));
        $all_tags = Tag::find([]);
        $likeliness = [];
        $likeliness_sum = 0;

        foreach($all_tags as $tag)
        {
            $id = $tag->getId();
            $name = $tag->Name;

            $likeliness[$id] = 0;
            foreach($tags as $target)
            {
                if(strlen($target) == 0)
                    break;

                if($target == $name)
                {
                    $likeliness[$id] += 1;
                    break;
                }

                $pos = strpos($name, $target);
                if($pos === false)
                    continue;


                $rate = strlen($target) / strlen($name);
                $likeliness[$id] += $rate;
            }
            $likeliness_sum += $likeliness[$id];
        }

        if($likeliness_sum == 0)
        {
            $response = new ApiResponse(200);
            $response->echo([]);
            return $response;
        }

        $priority = [];
        foreach($likeliness as $id => $rate)
        {
            $priority[] = [
                "tag" => Tag::get($id),
                "rate" => $rate
            ];
        }

        usort($priority, function($a, $b) {
            return $b["rate"] <=> $a["rate"];
        });


        $resources = [];

        foreach($priority as $option)
        {
            $matched = 0;
            $rate = $option["rate"] / $likeliness_sum;
            
            if($rate == 0)
                continue;

            $list = $option["tag"]->Resources();
            foreach($list as $link)
            {
                if(!isset($link->ApproveTime))
                    continue;

                $resource = $link->Resource;
                $id = $resource->getId();
                if(!isset($resources[$id]))
                {
                    $resTags = $resource->Tags();
                    $resources[$id] = [
                        "id" => $id,
                        "rate" => 0,
                        "tags" => 0,
                        "total_tags" => count($resTags),
                    ];
                }
                $resources[$id]["rate"] += $rate;
                $resources[$id]["tags"]++;
            }
        }

        foreach($resources as $key => $val)
        {
            $tag_rate = sqrt($val["tags"] /
                             $val["total_tags"]);
            $resources[$key]["rate"] *= $tag_rate;
        }

        usort($resources, function($a, $b){
            return $b["rate"] <=> $a["rate"];
        });


        $limit = 100;
        $max = $resources[0]["rate"];
        $min = $max / 10;
        //$min = 0;
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
