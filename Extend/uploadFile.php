<?php
namespace Extend;

use \RuntimeException;


/** Returns file upload error message
 * Thanks to Viktor and adam for this useful array
 * https://www.php.net/manual/en/features.file-upload.errors.php#115746
 */
function getFileErrorMsg($error) : string
{
    $phpFileUploadErrors = array(
        0 => 'There is no error, the file uploaded with success',
        1 => 'The uploaded file exceeds the upload_max_filesize directive in php.ini',
        2 => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form',
        3 => 'The uploaded file was only partially uploaded',
        4 => 'No file was uploaded',
        6 => 'Missing a temporary folder',
        7 => 'Failed to write file to disk.',
        8 => 'A PHP extension stopped the file upload.',
    );

    if(isset($phpFileUploadErrors[$error]))
        return $phpFileUploadErrors[$error];
    return "Unknown error";
}


/** Uploads file from $_FILES[$key]
 * @return ["name", "uri", "mime", "size"]
 */
function uploadFile(string $key,
                    ?array $mimes = null,
                    int $size_limit = 0)
{
    if(!isset($_FILES[$key]['error']) ||
       is_array($_FILES[$key]['error']))
    {
        var_dump($_FILES[$key]['error']);
        throw new RuntimeException
            ("Invalid input.");
    }

    if($_FILES[$key]['error'] != UPLOAD_ERR_OK)
    {
        
        throw new RuntimeException
            (getFileErrorMsg($_FILES[$key]['error']));
    }

    $size = $_FILES[$key]['size'];
    if($size_limit && $size > $size_limit)
    {
        throw new RuntimeException
            ("File too big.");
    }

    $finfo = new \finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($_FILES[$key]['tmp_name']);
    $ext = "";

    if($mimes)
    {
        $ext = array_search($mime, $mimes, true);
        if(!$ext)
        {
            throw new RuntimeException
                ("Unknown file type");
        }
        $ext = ".$ext";
    }


    $tmp = $_FILES[$key]['tmp_name'];

    $i = 0;
    $rnd = mt_rand(100, 999);
    $sha = sha1_file($tmp);
    $dir = "Uploads/" . substr($sha, 0, 2);
    if(!file_exists($dir))
        mkdir($dir);
    do
    {
        $i++;
        $target = "${dir}/${sha}_${i}_${rnd}${ext}";
    } while(file_exists($target));

    if (!move_uploaded_file($tmp, $target))
    {
        throw new RuntimeException
            ('Failed to move uploaded file.');
    }

    if(strlen($ext) > 0)
    {
        $name = pathinfo($_FILES[$key]["name"],
                         PATHINFO_FILENAME) . ".$ext";
    }
    else
    {
        $name = $_FILES[$key]["name"];
    }

    return [
        "name" => "$name",
        "uri" => $target,
        "mime" => $mime,
        "size" => $size
    ];
}


function uploadImage($key)
{
    $mimes = [
        'jpg' => 'image/jpeg',
        'png' => 'image/png',
        'gif' => 'image/gif',
    ];
    return uploadFile($key, $mimes, 1024*1024);
}
