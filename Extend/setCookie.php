<?php
namespace Extend;

function setCookie($name, $val, $args = [])
{
    if(!isset($args["SameSite"]))
        $args["SameSite"] = "Strict";
    if(!isset($args["Path"]))
        $args["Path"] = "/";

    \setcookie($name, $val, $args);
}
