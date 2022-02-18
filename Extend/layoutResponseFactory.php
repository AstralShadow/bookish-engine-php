<?php

namespace Extend;

use \Core\Responses\TemplateResponse;

function layoutResponseFactory(string $file,
                               int $code = 200)
{
    if($file == "404.html" && $code == 200)
        $code = 404;

    $response = new TemplateResponse
        (file: "_layout.html", code: $code);

    $response->setValue("_page", $file);

    return $response;
}

