<?php
namespace Model\Junction;

use Core\Entity;
use Core\Attributes\Table;
use Core\Attributes\PrimaryKey;
use Core\Attributes\Traceable;
use Core\Attributes\TraceLazyLoad;

use Model\Tag;


#[Table("TagRelations")]
#[PrimaryKey("Supertag", "Subtag")]
class ResourceTag extends Entity
{

    #[Traceable("Subtags")]
    public Tag $Supertag;

    #[Traceable("Supertags")]
    public Tag $Subtag;

    #[Traceable("LinkedTags")]
    public User $Creator;


    public function __construct(Tag $super,
                                Tag $sub,
                                User $creator)
    {
        $this->Supertag = $super;
        $this->Subtag = $sub;

        parent::__construct();
    }

}
