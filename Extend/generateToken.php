<?php

namespace Extend;

function generateToken(int $length = 36) : string
{
    $chars = array_merge(range('a', 'z'),
                         range('A', 'Z'),
                         range('0', '9'));
    $token = (string) base_convert(time(), 10, 32);

    for($i = strlen($token); $i < $length; ++$i)
    {
        $index = mt_rand(0, count($chars) - 1);
        $token .= $chars[$index];
    }
    return substr($token, 0, $length);
}
