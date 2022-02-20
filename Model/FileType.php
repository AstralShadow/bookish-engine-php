<?php
namespace Model;

use Core\Entity;
use Core\Attributes\Table;
use Core\Attributes\Traceable;
use Core\Attributes\PrimaryKey;
use Core\Attributes\TraceLazyLoad;

use Extend\Permissions;


#[Table("FileTypes")]
#[PrimaryKey("FileTypeId")]
class FileType extends Entity
{

    public string $MimeType;
    public bool $External;

    public static function findOrCreate(string $mime,
                                        bool $external)
                                            : FileType
    {
        $target = self::find([
            "MimeType" => $mime,
            "External" => $external
        ]);

        if(isset($target))
            return $target;

        return new FileType($mime, $external);
    }

    private function __construct(string $mime,
                                 bool $external)
    {
        $this->MimeType = $mime;
        $this->External = $external;
        parent::__construct();
    }

}
