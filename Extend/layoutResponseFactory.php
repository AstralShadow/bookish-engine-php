<?php

namespace Extend;

use \Core\Responses\TemplateResponse;
use Model\Session;

function layoutResponseFactory(string $file,
                               int $code = 200)
{
    if($file == "404.html" && $code == 200)
        $code = 404;

    $response = new TemplateResponse
        (file: "_layout.html", code: $code);

    $response->setValue("_page", $file);

    $user = Session::current()?->User;
    if($user)
    {
        $overview = $user->privateOverview();
        $overview["user"] = $overview["name"];
        unset($overview["name"]);
        
        if($user->has(Permissions::CanApproveResources))
            $overview["can_approve"] = "";
        else
            $overview["approve_element"] = "empty.html";

        if($user->has(Permissions::CanGiveRoles))
            $overview["can_give_roles"] = "";

        $response->setValues($overview);
    }

    return $response;
}

