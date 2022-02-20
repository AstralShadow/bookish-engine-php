<?php
namespace Extend;

function isValidString($input = null, $minLen = 0) : bool
{
    return isset($string)
        && is_string($input)
        && strlen(trim($input)) >= $minLen;
}
