<?php
namespace Model;

use Core\Entity;
use Core\Attributes\Table;
use Core\Attributes\Traceable;
use Core\Attributes\PrimaryKey;
use Core\Attributes\TraceLazyLoad;

use Extend\Permissions;
use Model\User;


#[TraceLazyLoad("\Model\Junction\UserResourceAccess",
                    "Readers")]

#[TraceLazyLoad("\Model\ResourceFeedback", "Feedback")]
#[TraceLazyLoad("\Model\ResourceReport", "Reports")]

#[TraceLazyLoad("\Model\Junction\ResourceTag", "Tags")]

#[Table("Resources")]
#[PrimaryKey("ResourceId")]
class Resource extends Entity
{

    public string $Name;
    #[Traceable("OwnedResources")]
    public User $Owner;
    public ?string $Description; // "info" in apis
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

        $this->approve($owner, false);

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
    
    public function overwiev()
    {
        $data = [
            "name" => $this->Name,
            "owner" => $this->Owner->Name,
            "created" => $this->CreateTime,

            "approved" => isset($this->ApproveTime),
            
            "tags" => [],
            "data_name" => $this->DataName,
            //"data_size" => 0,

            "preview_name" => $this->PreviewName
            //"preview_size" =>
        ];

        foreach($this->tags as $link)
        {
            $data["tags"][] = $link->Tag->data();
        }

        return $data;
    }

}
