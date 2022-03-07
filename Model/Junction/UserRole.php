<?php
namespace Model\Junction;

use Core\Entity;
use Core\Attributes\Table;
use Core\Attributes\PrimaryKey;
use Core\Attributes\Traceable;
use Core\Attributes\TraceLazyLoad;

use Model\User;
use Model\Role;

#[Table("UserRoles")]
#[PrimaryKey("User", "Role")]
class UserRole extends Entity
{

    #[Traceable("Users")]
    public Role $Role;

    #[Traceable("Roles")]
    public User $User;

    public \DateTime $CreateTime;
    public ?string $Reason;

    #[Traceable("AssignedRoles")]
    public User $AssignedBy;


    public function __construct(User $user, Role $role,
                                User $assigner,
                                ?string $reason = null)
    {
        $this->User = $user;
        $this->Role = $role;
        $this->Reaspon = $reason;
        $this->AssignedBy = $assigner;
        $this->CreateTime = new \DateTime();
        parent::__construct();
    }

}
