<?php
namespace Model;

use Core\Entity;
use Core\Attributes\Table;
use Core\Attributes\PrimaryKey;
use Core\Attributes\Traceable;
use Core\Attributes\TraceLazyLoad;

use Extend\Permissions;


#[Table("Roles")]
#[PrimaryKey("RoleId")]
#[TraceLazyLoad("\Model\Junction\UserRole", "Users")]
class Role extends Entity
{

    public string $Name;

    public bool $CanCreateRoles;
    public bool $CanGiveRoles;

    public bool $CanBlockUsers;
    public bool $CanResolveReports;

    public bool $CanApproveResources;
    public bool $CanCreateTags;
    public bool $CanApproveTags;
    public bool $CanProposeTags;


    public function __construct(string $name)
    {
        $this->Name = $name;
        parent::__construct();
    }

    public function has(int $permission) : bool
    {
        switch($permission)
        {
            case Permissions::CanCreateRoles:
                return $this->CanCreateRoles;

            case Permissions::CanGiveRoles:
                return $this->CanGiveRoles;

            case Permissions::CanBlockUsers:
                return $this->CanBlockUsers;

            case Permissions::CanResolveReports:
                return $this->CanResolveReports;

            case Permissions::CanApproveResources:
                return $this->CanApproveResources;

            case Permissions::CanCreateTags:
                return $this->CanCreateTags;

            case Permissions::CanApproveTags:
                return $this->CanApproveTags;

            case Permissions::CanProposeTags:
                return $this->CanProposeTags;

        }
    }

}

