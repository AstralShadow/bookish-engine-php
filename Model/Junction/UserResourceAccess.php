<?php
namespace Model\Junction;

use Core\Entity;
use Core\Attributes\Table;
use Core\Attributes\PrimaryKey;
use Core\Attributes\Traceable;
use Core\Attributes\TraceLazyLoad;

use Model\User;
use Model\Resource;

#[Table("UserResourceAccess")]
#[PrimaryKey("User", "Resource")]
class UserResourceAccess extends Entity
{

    #[Traceable("AccuredResources")]
    public User $User;

    #[Traceable("Readers")]
    public Resource $Resource;


    public \DateTime $AccureTime;
    public int $CurrencyValue;

    #[Traceable("ProvidedResources")]
    public ?User $ProvidedBy;


    public function __construct(User $user,
                                Resource $res,
                                int $price = 1,
                                ?User $provider = null)
    {
        $this->User = $user;
        $this->Resource = $res;

        $this->AccureTime = new \DateTime();
        
        $this->CurrencyValue = $price;
        $this->ProvidedBy = $provider;

        parent::__construct();
    }

}
