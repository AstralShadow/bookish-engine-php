<?php
namespace Model;

use Core\Entity;
use Core\Attributes\Table;
use Core\Attributes\PrimaryKey;
use Core\Attributes\TraceLazyLoad;

use Extend\Permissions;
use Model\User;


#[TraceLazyLoad("\Model\Junction\UserResourceAccess",
                    "Readers")]

#[TraceLazyLoad("\Model\ResourceFeedback", "Feedback")]
#[TraceLazyLoad("\Model\ResourceReport", "Reports")]

#[TraceLazyLoad("\Model\Junction\ResourceTag", "Tags")]

#[Table("Resource")]
#[PrimaryKey("ResourceId")]
class Resource extends Entity
{

    public string $Name;
    #[Traceable("OwnedResources")]
    public User $Owner;
    public ?string $Description;
    public \DateTime $CreateTime;
    
    public ?FileType $DataType;
    public ?string $DataName;
    public ?string $Data;

    public ?FileType $PreviewType;
    public ?string $PreviewName;
    public ?string $Preview;

    public \DateTime $ApproveTime;
    #[Traceable("ApprovedResources")]
    public ?User $ApprovedBy;
    public ?string $ApproveNote;


    public function __construct(string $name, User $owner)
    {
        $this->Name = $name;
        $this->Owner = $owner;
        $this->CreateTime = new \DateTime();

        $this->approve($owner);

        parent::__construct();
    }

    public function approve(User $user, bool $save = true)
    {
        if($user->has(Permissions::CanApproveResources))
        {
            $this->ApproveTime = new \DateTime();
            $this->ApprovedBy = $user;

            if($save)
                $this->save();
        }
    }

}
