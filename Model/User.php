<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Model;

use Core\Entity;
use Core\Attributes\Table;
use Core\Attributes\PrimaryKey;
use Core\Attributes\TraceLazyLoad;

#[Table("Users")]
#[PrimaryKey("UserId")]
#[TraceLazyLoad("\Model\Session", "sessions")]
class User extends Entity
{

    public string $Name;
    private string $PasswordHash;

    public string? $Avatar = null;
    public \DateTime $CreateTime;

    public \DateTime? $BlockTime = null;
    public User? $BlockedBy = null;
    public string? $BlockReason = null;

    public function __construct(string $name,
                                string $password)
    {
        $this->Name = $name;
        $this->PasswordHash =
            password_hash($password, PASSWORD_BCRYPT);
        $this->CreateTime = new \DateTime();
        parent::__construct();
    }

    public static function login(string $name,
                                 string $password): ?User
    {
        $user = self::find(["name" => $name])[0] ?? null;

        if(!isset($user))
            return null;

        if(!password_verify($password, $user->password))
            return null;
        
        return $user;
    }

    public static function exists(string $name): bool
    {
        return count(self::find(["name" => $name]));
    }

}
