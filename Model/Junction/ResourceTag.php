<?php
namespace Model\Junction;

use Core\Entity;
use Core\Attributes\Table;
use Core\Attributes\PrimaryKey;
use Core\Attributes\Traceable;
use Core\Attributes\TraceLazyLoad;

use Model\Resource;
use Model\Tag;

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
        $this->User = $user;
        $this->Resource = $res;

        $this->AccureTime = new DateTime();
        
        $this->CurrencyValue = $price;
        $this->ProvidedBy = $provider;

        $this->approve($proposer, false);

        parent::__construct();
    }


    public function approve(User $user, bool $save = true)
    {
        if($user->has(Permissions::CanApproveTags))
        {
            $this->ApproveTime = new \DateTime();
            $this->ApprovedBy = $user;

            if($save)
                $this->save();
        }
    }

}
