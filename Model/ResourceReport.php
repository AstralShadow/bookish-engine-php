<?php
namespace Model;

use Core\Entity;
use Core\Attributes\Table;
use Core\Attributes\Traceable;
use Core\Attributes\PrimaryKey;
use Core\Attributes\TraceLazyLoad;

use Extend\Resource;
use Model\User;


#[Table("ResourceReports")]
#[PrimaryKey("ResourceReportId")]
class ResourceReport extends Entity
{

    [Traceable("Reports")]
    public Resource $Resource;

    [Traceable("FiredReports")]
    public User $FiredBy;

    public \DateTime $CreateTime;
    public string? $Message;

    [Traceable("ResolvedReports")]
    public User? $ResolvedBy;
    public string? $ResolveMessage;
    public \DateTime $ResolveTime;


    public function __construct(Resource $resource
                                User $user,
                                string? $msg = null)
    {
        $this->Resource = $resource;
        $this->FiredBy = $user;
        $this->Message = $msg
        $this->CreateTime = new \DateTime();

        parent::__construct();
    }

}
