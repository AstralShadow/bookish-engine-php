<?php
namespace Extend;

use Model\Session;
use function Extend\generateToken;
use function Extend\isValidString;


// Uses double submit technique.
class CSRFTokenManager
{
    const TOKEN_COOKIE = "LearningResourcesCSRFToken";

    private static bool $valid;
    private static string $token;

    // If the request states current session with
    // session token and not cookie, then it can
    // serve as CSRF protection as well.
    // No need to do a full CSRF check
    public static function weak_check() : bool
    {
        if(Session::fromPOST() != null)
            return true;

        return self::check();
    }

    public static function check() : bool
    {
        if(isset(self::$valid))
            return self::$valid;

        self::$valid = false;
        if(isValidString($_POST["csrf"], 42))
        {
            $cookie = $_COOKIE[self::TOKEN_COOKIE];
            self::$valid = $_POST["csrf"] == $cookie;
        }

        return self::$valid;
    }

    public static function get() : string
    {
        self::check();

        if(!isset(self::$token))
        {
            self::$token = generateToken(42);
            setcookie(self::TOKEN_COOKIE, self::$token, [
                'samesite' => 'Strict'
            ]);
        }

        return self::$token;
    }
    

}
