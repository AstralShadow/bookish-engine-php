<?php
namespace Model;

use Core\Entity;
use Core\Attributes\Table;
use Core\Attributes\Traceable;
use Core\Attributes\PrimaryKey;
use Core\Attributes\TraceLazyLoad;

use Model\Resource;
use Model\User;


#[Table("ResourceFeedback")]
#[PrimaryKey("ResourceFeedbackId")]
class ResourceFeedback extends Entity
{

    #[Traceable("Feedback")]
    public Resource $Resource;

    #[Traceable("ProvidedFeedback")]
    public User $User;

    public \DateTime $CreateTime;
    public string $Message;

    public function __construct(Resource $resource,
                                User $user,
                                string $msg)
    {
        $this->Resource = $resource;
        $this->User = $user;
        $this->Message = $msg;
        $this->CreateTime = new \DateTime();

        parent::__construct();
    }

}
