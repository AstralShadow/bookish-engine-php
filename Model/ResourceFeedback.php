<?php
namespace Model;

use Core\Entity;
use Core\Attributes\Table;
use Core\Attributes\Traceable;
use Core\Attributes\PrimaryKey;
use Core\Attributes\TraceLazyLoad;

use Extend\Resource;
use Model\User;


#[Table("ResourceFeedback")]
#[PrimaryKey("ResourceFeedbackId")]
class ResourceFeedback extends Entity
{

    [Traceable("Feedback")]
    public Resource $Resource;

    [Traceable("ProvidedFeedback")]
    public User $User;

    public \DateTime $CreateTime;
    public int $Rating;
    public string? $Message;

    public function __construct(Resource $resource
                                User $user,
                                int $rating,
                                string? $msg = null)
    {
        $this->Resource = $resource;
        $this->User = $user;
        $this->Rating = $rating;
        $this->Message = $msg;
        $this->CreateTime = new \DateTime();

        parent::__construct();
    }

}
