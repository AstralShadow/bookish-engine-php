<?php
namespace Controllers;

use Core\Request;
use Core\RequestMethods\GET;
use Core\RequestMethods\PUT;
use Core\RequestMethods\POST;
use Core\RequestMethods\DELETE;
use Core\RequestMethods\Fallback;
use Core\RequestMethods\StartUp;
use Core\Responses\InstantResponse;

use function Extend\layoutResponseFactory as Page;
use function Extend\generateToken;
use function Extend\redirect;
use Extend\CSRFTokenManager as CSRF;
use Model\Session;
use Model\User;

class Account
{

    #[GET("/login")]
    #[POST("/login")]
    public static function login(Request $r)
    {
        $session = Session::fromCookie();
        if(isset($session))
            return redirect();

        $response = Page("login.html");
        $response->setValue("csrf", CSRF::get());

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

        if(!CSRF::check())
        {
            return $error("Невалидна сесия. " . 
                          "Моля опитайте отново.");
        }

        $state = \API\Session::login();
        $code = $state->getCode();
        $output = $state->getOutput();

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
        $session = Session::fromCookie();
        if(isset($session))
            return redirect();

        $response = Page("register.html");
        $response->setValue("csrf", CSRF::get());

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

        if(!CSRF::check())
        {
            return $error("Невалидна сесия. " . 
                          "Моля опитайте отново.");
        }

        if($_POST["password"] !== $_POST["password2"])
            return $error("Въвели сте различни пароли.");

        $state = \API\User::createUser();
        $code = $state->getCode();
        $output = $state->getOutput();

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

    #[GET("/logout")]
    #[POST("/logout")]
    public static function logout()
    {
        $session = Session::fromCookie();
        if(isset($session))
            Session::delete($session->getId());

        return redirect();
    }

}
