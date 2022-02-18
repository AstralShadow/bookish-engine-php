<?php

namespace Extend;

use \Core\Responses\InstantResponse;

function redirect(?string $uri = null,
                  int $code = 303)
{
    $next = $uri ?? $_GET["next"] ?? "./";
    $response = new InstantResponse($code);
    $response->setHeader("Location", $next);
    return $response;
}

