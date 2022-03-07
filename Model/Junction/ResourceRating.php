<?php
namespace Model\Junction;

use Core\Entity;
use Core\Attributes\Table;
use Core\Attributes\Traceable;
use Core\Attributes\PrimaryKey;
use Core\Attributes\TraceLazyLoad;

use Model\Resource;
use Model\User;


#[Table("ResourceRatings")]
#[PrimaryKey("Resource", "User")]
class ResourceRating extends Entity
{

    #[Traceable("Rating")]
    public Resource $Resource;

    #[Traceable("ProvidedRating")]
    public User $User;

    public int $Rating;

    public function __construct(Resource $resource,
                                User $user,
                                int $rating)
    {
        $this->Resource = $resource;
        $this->User = $user;
        $this->Rating = $rating;

        parent::__construct();
    }

}
