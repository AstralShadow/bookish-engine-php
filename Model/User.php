<?php
namespace Model;

use Core\Entity;
use Core\Attributes\Table;
use Core\Attributes\PrimaryKey;
use Core\Attributes\TraceLazyLoad;

use Extend\Permissions;


#[Table("Users")]
#[PrimaryKey("UserId")]
#[TraceLazyLoad("\Model\Session", "Sessions")]
#[TraceLazyLoad("\Model\Resource",
                    "OwnedResources")]
#[TraceLazyLoad("\Model\UserResourceAccess",
                    "AccuredResources")]
#[TraceLazyLoad("\Model\ResourceFeedback",
                    "ProvidedFeedback")]
#[TraceLazyLoad("\Model\ResourceReport",
                    "FiredReports")]
#[TraceLazyLoad("\Model\ResolvedReports",
                    "ResolvedReports")]
#[TraceLazyLoad("\Model\Junction\UserRole",
                    "AssignedRoles")]
#[TraceLazyLoad("\Model\Junction\UserRole",
                    "Roles")]
class User extends Entity
{

    public string $Name;
    protected string $Password;

    # public ?FileType $AvatarType;
    public ?string $Avatar = null;
    public \DateTime $CreateTime;

    public ?\DateTime $BlockTime = null;
    public ?User $BlockedBy = null;
    public ?string $BlockReason = null;


    public function __construct(string $name,
                                string $password)
    {
        $this->Name = $name;
        $this->Password =
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

        if(!password_verify($password, $user->Password))
            return null;
        
        return $user;
    }

    public static function exists(string $name): bool
    {
        return count(self::find(["name" => $name]));
    }

    public function has(int $perm) : bool
    {
        foreach($this->Roles as $link)
        {
            if($link->Role->has($perm))
                return true;
        }

        return false;
    }
}
