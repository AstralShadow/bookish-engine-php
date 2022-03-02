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

    public static
    function keywordsToTagsStrict($words, $extra = false)
    {
        $tags_raw = [];
        foreach($words as $word)
        {
            $tag = Tag::find(["Name" => $word])[0]??null;
            if($tag == null)
                continue;

            $tags_raw[] = $tag;
        }

        $tags = array_unique($tags_raw);

        if(!$extra)
            return $tags;

        return array_map(function($tag)
        {
            return ["tag" => $tag, "rate" => 1];
        }, $tags);
    }

    /** Returns tags sorted by match rate.
     * If extra is true returns array of
     * ["tag" => Tag, "rate" => float]
     */
    public static 
    function keywordsToTags($words, $extra = false)
    {
        $all_tags = Tag::find([]);
        $data = [];

        foreach($all_tags as $tag)
        {
            $tag_data = [
                "tag" => $tag,
                "rate" => 0
            ];
            $name = $tag->Name;
            
            foreach($words as $word)
            {
                if(strlen($word) == 0)
                    break;

                if(strpos($name, $word) === false)
                    break;

                $tag_data["rate"] += strlen($word)
                                     / strlen($name);
            }

            if($tag_data["rate"] > 0)
                $data[] = $tag_data;
        }

        usort($data, function($a, $b) {
            return $b["rate"] <=> $a["rate"];
        });

        if($extra)
            return $data;

        $data = array_map(function($tag_data)
        {
            return $tag_data["tag"];
        }, $data);
    }

    public static
    function find(array $tags, $useExtra = false)
    {
        $collection = [];

        foreach($tags as $data)
        {
            if(!$useExtra)
            {
                $collection[] = $data->Resources();
                continue;
            }

            $collection[] = $data["tag"]->Resources();
        }
        
        $collection = array_merge(...$collection);
        $collection = array_map(function($link)
        {
            return $link->Resource;
        }, $collection);

        return self::findIn($tags,
                            $collection,
                            $useExtra);
    }

    public static
    function findIn(array $tags_data,
                    array $collection,
                    $usedExtra = false)
    {
        if(!$usedExtra)
        {
            $tags_data = array_map(function($tag) {
                return ["tag" => $tag, "rate" => 1];
            }, $tags_data);
        }

        $total_tag_rate = 0;
        foreach($tags_data as $data)
            $total_tag_rate += $data["rate"];

        $results = [];
        
        foreach($collection as $resource)
        {
            $id = $resource->getId();
            $results[$id] = [
                "resource" => $resource,
                "rate" => 0
            ];
            $tags = array_map(function($link) {
                return $link->Tag;
            }, $resource->Tags());
            $matched_tags = 0;
            $total_tags = count($tags);
            if($total_tags == 0)
                continue;

            foreach($tags_data as $tag_data)
            {
                $tag = $tag_data["tag"];
                if(!in_array($tag, $tags))
                    continue;
                $matched_tags++;
                $results[$id]["rate"] +=$tag_data["rate"]
                                       / $total_tag_rate;
            }
            $results[$id]["rate"] *= $matched_tags
                                     / $total_tags;
        }

        $results = array_filter($results, function($r)
        {
            return $r["rate"] > 0;
        });
        
        usort($results, function($a, $b) {
            return $b["rate"] <=> $a["rate"];
        });

        return array_map(function($data)
        {
            return $data["resource"];
        }, $results);
    }


    #[GET("/search")]
    #[GET("/filter")]
    public static function printEmpty()
    {
        $response = new ApiResponse(200);
        $response->echo([]);
        return $response;
    }

    #[GET("/search/{tags}")]
    public static function searchByTags(Request $req)
    {
        $query = mb_strtolower($req->tags);
        $keywords = array_map(function($a){
            return urldecode($a);
        }, explode('+', $query));

        $extra = true;
        $tags = self::keywordsToTags($keywords, $extra);
        $resources = self::find($tags, $extra);

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

    #[GET("/filter/{tags}")]
    public static function filterByTags(Request $req)
    {
        $query = mb_strtolower($req->tags);
        $keywords = array_map(function($a){
            return urldecode($a);
        }, explode('+', $query));

        $extra = true;
        $tags = self::keywordsToTagsStrict
            ($keywords, $extra);
        $resources = self::find($tags, $extra);

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

}
