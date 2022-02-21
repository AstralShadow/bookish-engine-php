<?php
namespace Extend;

use Core\Responses\ApiResponse;

function APIError($code, $error = null)
{
    $response = new ApiResponse($code);
    if($error)
        $response->echo([ "error" => $error ]);
    return $response;
};
