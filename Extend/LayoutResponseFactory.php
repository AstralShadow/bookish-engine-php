<?php

namespace Extend;

use \Core\Responses\TemplateResponse;

function LayoutResponseFactory(string $file,
                               int $code = 200)
{
    $response = new TemplateResponse
        (file: "_layout.html", code: $code);

    $response->setValue("_page", $file);

    return $response;
}

