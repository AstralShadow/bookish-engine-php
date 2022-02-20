<?php
namespace Extend;

use Core\Responses\ApiResponse;

function APIError($code, $error)
{
    $response = new ApiResponse($code);
    $response->echo([ "error" => $error ]);
    return $response;
};
