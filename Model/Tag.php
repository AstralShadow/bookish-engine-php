<?php

namespace Model;

use \Core\Entity;
use \Core\Attributes\Table;
use \Core\Attributes\PrimaryKey;
use \Core\Attributes\Traceable;
use function \Extend\generateToken;


#[TraceLazyLoad("\Model\Junction\ResourceTag",
                    "Resources")]

#[TraceLazyLoad("\Model\Junction\TagRelation",
                    "Supertags")]
#[TraceLazyLoad("\Model\Junction\TagRelation",
                    "Subtags")]


#[Table("Tags")]
#[PrimaryKey("TagId")]
class Tag extends Entity
{
    public string $Name;
    public string? $Description;

    #[Traceable("CreatedTags")]
    public User $Creator;
    public \DateTime $CreateTime;

    public function __construct(User $user,
                                string $name,
                                string? $info = null)
    {
        $this->Name = $name;
        $this->Description = $info;
        $this->Creator = $user;
        $this->CreateTime = new \DateTime();

        parent::__construct();
    }
}

