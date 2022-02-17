<?php

require "Core/autoload.php";

$router = new Core\Router();
$router->add("\Controllers\Home", "/");
$router->add("\Controllers\Session", "api/session");
$router->add("\Controllers\User", "api/user");

$engine = new Core\Controller($router);

$engine->usePDO(
    "mysql:host=localhost;dbname=learning_res_simple",
    "student_app_2", "student_app_2_password"
);

$engine->run();
