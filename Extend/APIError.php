<?php
namespace Extend;

use Core\ResponsesApiResponse;

function APIError($code, $error)
{
    $response = new ApiResponse($code);
    $response->echo([ "error" => $error ]);
    return $response;
};
