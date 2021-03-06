<?php

namespace Model;

use \Core\Entity;
use \Core\Attributes\Table;
use \Core\Attributes\PrimaryKey;
use \Core\Attributes\Traceable;
use function \Extend\generateToken;
use function \Extend\setCookie;


#[Table("Sessions")]
#[PrimaryKey("SessionId")]
class Session extends Entity
{
    const COOKIE_NAME = "LearningResourcesSession";
    const POST_KEY = "token";

    protected string $Token;
    public \DateTime $Created;

    #[Traceable("Sessions")]
    public User $User;

    public function __construct(User $user)
    {
        $this->User = $user;
        $this->Token = generateToken();
        $this->Created = new \DateTime();
        parent::__construct();
    }

    public function token() : string
    {
        return $this->Token;
    }

    public static function fromToken(string $token)
        : ?Session
    {
        $sessions = self::find(["Token" => $token]);
        return $sessions ? $sessions[0] : null;
    }

    public static function fromCookie() : ?Session
    {
        $token = $_COOKIE[self::COOKIE_NAME] ?? null;

        if(isset($token))
        {
            $session = self::fromToken($token);
            if(isset($session))
            {
                return $session;
            }
        }

        return null;
    }

    public function saveInCookie() : void
    {
        setCookie(self::COOKIE_NAME, $this->Token);
    }

    public static function fromPOST() : ?Session
    {
        if(is_string($_POST[self::POST_KEY] ?? null))
        {
            $session = self::fromToken
                             ($_POST[self::POST_KEY]);

            if(isset($session))
                return $session;
        }
        return null;
    }

    public static function fromPOSTorCookie() : ?Session
    {
        return self::fromPOST() ?? self::fromCookie();
    }

    public static function current() : ?Session
    {
        return self::fromPOSTorCookie();
    }

}

