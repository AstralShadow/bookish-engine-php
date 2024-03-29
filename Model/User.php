<?php
namespace Model;

use Core\Entity;
use Core\Attributes\Table;
use Core\Attributes\PrimaryKey;
use Core\Attributes\TraceLazyLoad;
use Core\Attributes\Traceable;

use Extend\Permissions;


#[TraceLazyLoad("\Model\Session", "Sessions")]

#[TraceLazyLoad("\Model\Resource", "OwnedResources")]
#[TraceLazyLoad("\Model\Resource", "ApprovedResources")]
#[TraceLazyLoad("\Model\Junction\UserResourceAccess",
                    "AccuredResources")]
#[TraceLazyLoad("\Model\Junction\UserResourceAccess",
                    "ProvidedResources")]

#[TraceLazyLoad("\Model\Junction\ResourceRating",
                    "ProvidedRating")]

#[TraceLazyLoad("\Model\ResourceFeedback",
                    "ProvidedFeedback")]

#[TraceLazyLoad("\Model\ResourceReport",
                    "FiredReports")]
#[TraceLazyLoad("\Model\ResolvedReport",
                    "ResolvedReports")]

#[TraceLazyLoad("\Model\Junction\UserRole",
                    "AssignedRoles")]
#[TraceLazyLoad("\Model\Junction\UserRole",
                    "Roles")]

#[TraceLazyLoad("\Model\Junction\ResourceTag",
                    "ProposedTags")]
#[TraceLazyLoad("\Model\Junction\ResourceTag",
                    "ApprovedTags")]
#[TraceLazyLoad("\Model\Junction\TagRelation",
                    "LinkedTags")]

#[Table("Users")]
#[PrimaryKey("UserId")]
class User extends Entity
{

    const INITIAL_SCROLLS = 0;

    public string $Name;
    protected string $Password;
    public int $Scrolls;

    public ?string $Avatar = null;
    public ?string $AvatarMime;
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
        $this->Scrolls = self::INITIAL_SCROLLS;
        parent::__construct();
    }

    public static function login(string $name,
                                 string $password): ?User
    {
        $user = self::find(["Name" => $name])[0] ?? null;

        if(!isset($user))
            return null;

        if(!password_verify($password, $user->Password))
            return null;
        
        return $user;
    }

    public static function exists(string $name): bool
    {
        return count(self::find(["Name" => $name]));
    }

    public function has(int $perm) : bool
    {
        foreach($this->Roles() as $link)
        {
            if($link->Role->has($perm))
                return true;
        }

        return false;
    }

    public function overview()
    {
        $data = [
            "name" => $this->Name,
            "role" => $this->roleName(),
            "avatar" => $this->avatarUri()
        ];

        return $data;
    }

    public function privateOverview()
    {
        $data = $this->overview();
        $data["scrolls"] = $this->Scrolls;
        return $data;
    }

    public function avatarUri() : ?string
    {
        if(!isset($this->Avatar) ||
            !file_exists($this->Avatar))
        {
            return null;
        }

        $time = filemtime($this->Avatar);

        return "/api/user/" . urlencode($this->Name) . "/avatar?" . $time;
    }

    public function roleName() : string
    {
        if($this->has(Permissions::CanGiveRoles))
            return "Админ";
        if($this->has(Permissions::CanApproveResources))
            return "Модератор";
        return "Потребител";
    }

}
