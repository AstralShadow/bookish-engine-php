<?php
namespace Extend;

function isValidString(&$input, $minLen = 0) : bool
{
    return isset($input)
        && is_string($input)
        && strlen(trim($input)) >= $minLen;
}

/*
function isValidPostString($key, $minLen = 0) : bool
{
    return isset($_POST[$key])
        && is_string($_POST[$key])
        && strlen(trim($key)) >= $minLen;
}
*/
