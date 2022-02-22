<?php
namespace Model\Junction;

use Core\Entity;
use Core\Attributes\Table;
use Core\Attributes\PrimaryKey;
use Core\Attributes\Traceable;
use Core\Attributes\TraceLazyLoad;

use Extend\Permissions;

use Model\Resource;
use Model\Tag;
use Model\User;

#[Table("ResourceTags")]
#[PrimaryKey("Resource", "Tag")]
class ResourceTag extends Entity
{

    #[Traceable("Tags")]
    public Resource $Resource;

    #[Traceable("Resources")]
    public Tag $Tag;

    #[Traceable("ProposedTags")]
    public User $ProposedBy;
    public \DateTime $ProposeTime;

    #[Traceable("ApprovedTags")]
    public ?User $ApprovedBy;
    public ?\DateTime $ApproveTime;

    public function __construct(Resource $res,
                                Tag $tag,
                                User $proposer)
    {
        $this->Resource = $res;
        $this->Tag = $tag;

        $this->ProposedBy = $proposer;
        $this->ProposeTime = new \DateTime();

        $this->approve($proposer, false);

        parent::__construct();
    }


    public function approve(User $user, bool $save = true)
    {
        $owner = $this->Resource->Owner;
        $perm = $user->has(Permissions::CanApproveTags)
                || $user->getId() == $owner->getId();

        if($perm)
        {
            $this->ApproveTime = new \DateTime();
            $this->ApprovedBy = $user;

            if($save)
                $this->save();
        }
    }

}
