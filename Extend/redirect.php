<?php

namespace Extend;

use \Core\Responses\BufferedResponse;

function redirect(?string $uri = null,
                  int $code = 303)
{
    $next = $uri ?? $_GET["next"] ?? "./";
    $response = new BufferedResponse($code);
    $response->setHeader("Location", $next);
    return $response;
}

