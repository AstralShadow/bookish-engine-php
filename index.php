<?php

# define("DEBUG_AUTOLOAD_LOG", 1);
# define("DEBUG_PRINT_QUERY_TYPES", 1);
# define("DEBUG_STATUS_STRING", 1);

$start = microtime(1);


require "Extend/layoutResponseFactory.php";
require "Extend/generateToken.php";
require "Extend/redirect.php";
require "Extend/Permissions.php";
require "Extend/APIError.php";
require "Extend/isValidString.php";
require "Extend/CSRFTokenManager.php";
require "Extend/uploadFile.php";
require "Extend/shrinkAvatar.php";

require "Core/autoload.php";

$router = new Core\Router();
$router->add("\Controllers\Home", "/");
$router->add("\Controllers\Account", "/");
$router->add("\Controllers\User", "/user");

$router->add("\API\Session", "api/session");
$router->add("\API\User", "api/user");
$router->add("\API\Resource", "api/resource");
$router->add("\API\Search", "api/search");

$engine = new Core\Controller($router);

$engine->usePDO(
    "mysql:host=localhost;dbname=learning_res_simple",
    "student_app_2", "student_app_2_password"
);

$engine->run();

if(defined("DEBUG_STATUS_STRING"))
{
    echo "( ͡° ͜ʖ ͡°) <br />\n";

    $end = microtime(1);
    echo "Time: ";
    echo ($end - $start) * 1000;
    echo "ms <br />\n";

    echo "Memory: " . Core\getMemoryUsage() . "<br />\n";
    \Core\Entity::printDebugStats();
}
