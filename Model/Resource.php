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
    
    public ?string $DataName;
    public ?string $DataMime;
    public int $DataSize = 0;
    public ?string $Data;

    public ?string $PreviewName;
    public ?string $PreviewMime;
    public int $PreviewSize = 0;
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
    
    public function overview(bool $full = false)
    {
        $created = $this->CreateTime->format('Y-m-d');
        $data = [
            "id" => $this->getId(),
            "name" => $this->Name,
            "owner" => $this->Owner->Name,
            "created" => $created,
            "info" => $this->Description,

            "approved" => isset($this->ApproveTime),
            
            "tags" => []
        ];

        foreach($this->Tags() as $link)
            $data["tags"][] = $link->Tag->data();

        if($full)
        {
            $data["data_name"] = $this->DataName;
            $data["data_size"] = $this->DataSize;
            $data["data_mime"] = $this->DataMime;

            $data["preview_name"] = $this->PreviewName;
            $data["preview_size"] = $this->PreviewSize;
            $data["preview_mime"] = $this->PreviewMime;
        }

        return $data;
    }

}
