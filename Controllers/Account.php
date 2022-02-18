<?php
namespace Controllers;

use Core\Request;
use Core\RequestMethods\GET;
use Core\RequestMethods\PUT;
use Core\RequestMethods\POST;
use Core\RequestMethods\DELETE;
use Core\RequestMethods\Fallback;
use Core\RequestMethods\StartUp;

use function Extend\layoutResponseFactory as Page;
use function Extend\generateToken;
use Model\Session;
use Model\User;

class Account
{
    const TOKEN_COOKIE = "LearningResourcesCSRFToken";

    private static bool $isValidCSRFToken = false;


    #[StartUp]
    public static function verifyCSRFToken()
    {
        if(!isset($_POST["csrf"]))
            return;

        $token = $_COOKIE[self::TOKEN_COOKIE] ?? null;

        if($token === $_POST["csrf"])
            self::$isValidCSRFToken = true;
    }

    private static
    function generateToken() : string
    {
        $token = generateToken();
        setcookie(self::TOKEN_COOKIE, $token);

        return $token;
    }

    #[GET("/login")]
    #[POST("/login")]
    public static function login(Request $r)
    {
        $response = Page("login.html");
        $response->setValue
            ("csrf", self::generateToken());

        if(!isset($_POST["name"], $_POST["password"])
            || !is_string($_POST["name"])
            || !is_string($_POST["password"]))

        {
            return $response;
        }

        $error = function(string $msg) use (&$response)
        {
            $response->setValue("error", $msg);
            return $response;
        };

        if(!self::$isValidCSRFToken)
            return $error("Невалидна сесия. " . 
                          "Моля опитайте отново.");


        $api_response = \API\Session::login();
        $code = $api_response->getCode();
        $output = $api_response->getOutput();

        if($code != 200)
            return $error($output["error"]);

        $next = $_GET["next"] ?? "./";

        $flw = <<<EOD
            <script defer>
                setTimeout(() => {
                    location.replace("{$next}")
                }, 1000);
            </script>
            EOD;
        return $error("Влязохте успешно.$flw");
    }

    #[GET("/register")]
    #[POST("/register")]
    public static function register(Request $r)
    {
        $response = Page("register.html");
        $response->setValue
            ("csrf", self::generateToken());

        if(!isset($_POST["name"],
                  $_POST["password"],
                  $_POST["password2"]))
        {
            return $response;
        }

        $error = function(string $msg) use (&$response)
        {
            $response->setValue("error", $msg);
            return $response;
        };

        if(!self::$isValidCSRFToken)
            return $error("Невалидна сесия. " . 
                          "Моля опитайте отново.");

        if($_POST["password"] !== $_POST["password2"])
            return $error("Въвели сте различни пароли.");

        $api_response = \API\User::createUser();
        $code = $api_response->getCode();
        $output = $api_response->getOutput();

        if($code != 200)
            return $error($output["error"]);

        $flw = <<<EOD
            <script defer>
                setTimeout(() => {
                    location.replace("./login")
                }, 1000);
            </script>
            EOD;
        return $error("Регистрацията беше успешна.$flw");
    }

}
