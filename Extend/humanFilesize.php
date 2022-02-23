<?php
namespace Extend;

/** Prints filesize in human readable way
 * Thanks to rommel for this useful function:
 * https://www.php.net/function.filesize#106569
 */
function humanFilesize($bytes, $decimals = 2) {
    $sz = 'BKMGTP';
    $factor = floor((strlen($bytes) - 1) / 3);
    return sprintf("%.{$decimals}f", $bytes / pow(1000, $factor)) . @$sz[$factor];
}
