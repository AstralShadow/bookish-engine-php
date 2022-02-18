<?php

define("DEBUG_AUTOLOAD_LOG", 1);
define("DEBUG_PRINT_QUERY_TYPES", 1);

require "Extend/layoutResponseFactory.php";
require "Extend/generateToken.php";
require "Core/autoload.php";

$router = new Core\Router();
$router->add("\Controllers\Home", "/");
$router->add("\Controllers\Account", "/");

$router->add("\API\Session", "api/session");
$router->add("\API\User", "api/user");

$engine = new Core\Controller($router);

$engine->usePDO(
    "mysql:host=localhost;dbname=learning_res_simple",
    "student_app_2", "student_app_2_password"
);

$engine->run();
