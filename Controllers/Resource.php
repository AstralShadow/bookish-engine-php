<?php
namespace Controllers;

use Core\Request;
use Core\RequestMethods\GET;
use Core\RequestMethods\PUT;
use Core\RequestMethods\POST;
use Core\RequestMethods\DELETE;
use Core\RequestMethods\Fallback;
use Core\RequestMethods\StartUp;
use Core\RequestMethods\RequestMethod;

use function Extend\layoutResponseFactory as Page;
use function Extend\redirect;
use function Extend\humanFilesize;
use Extend\CSRFTokenManager as CSRF;

use Model\Session;
use Model\User;
use Model\Resource as MResource;


class Resource
{

    #[GET("/{id}")]
    public static function index(Request $req)
    {
        $id = $req->id;
        $resource = MResource::get($id);
        if(!$resource)
            return Page("404.html", 404);


        $user = Session::current()?->User;
        if(!$user)
            return redirect("/login?next=/resource/".$id);

        $html = Page("resource.html", 200);

        $html->setValue("user", $user->Name);
        $html->setValue("role", $user->roleName());
        $html->setValue("scrolls", $user->Scrolls);

        $data = $resource->overview(true);
        
        $data["tags"] = json_encode($data["tags"]);
        $data["data_size"] = humanFilesize 
            ($data["data_size"]);
        $data["preview_size"] = humanFilesize 
            ($data["preview_size"]);
        $data["preview_url"] = "/api/resource/$id/preview";
        $data["data_url"] = "/api/resource/$id/download";

        $html->setValues($data);

        return $html;
    }

}
