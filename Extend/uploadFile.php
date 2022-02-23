<?php
namespace Extend;

/** Uploads file from $_FILES[$key]
 * @return ["uri", "mime", "size"]
 */
function uploadFile(string $key,
                    ?array $mimes = null,
                    int $size_limit = 0)
{
    if(!isset($_FILES[$key]['error']) ||
       is_array($_FILES[$key]['error']))
    {
        throw new RuntimeException
            ("Invalid input.");
    }

    if($_FILES[$key]['error'] != UPLOAD_ERR_OK)
    {
        throw new RuntimeException
            ("Invalid input.");
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
