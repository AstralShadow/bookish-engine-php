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
        $overview = $user->privateOverwiev();
        $overview["user"] = $overview["name"];
        unset($overview["name"]);
        $response->setValues($overview);
    }

    return $response;
}

