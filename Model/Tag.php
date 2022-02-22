<?php

namespace Model;

use \Core\Entity;
use \Core\Attributes\Table;
use \Core\Attributes\PrimaryKey;
use \Core\Attributes\Traceable;
use \Core\Attributes\TraceLazyLoad;
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
    public ?string $Description;

    #[Traceable("CreatedTags")]
    public User $Creator;
    public \DateTime $CreateTime;

    public function __construct(User $user,
                                string $name,
                                ?string $info = null)
    {
        $this->Name = mb_strtolower($name);
        $this->Description = $info;
        $this->Creator = $user;
        $this->CreateTime = new \DateTime();

        parent::__construct();
    }

    public function data()
    {
        $data = [ "name" => mb_strtolower($this->Name) ];
        if(isset($this->Description))
            $data["info"] = $this->Description;

        return $data;
    }

    public static function exists($name) : bool
    {
        return count(self::find([
            "Name" => mb_strtolower($name)
        ]));
    }
}

