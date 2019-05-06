<?php

namespace App\Helper;


class StringHelper
{
    public static function normalize(?string $value)
    {
        $value = str_replace("[", "", $value);
        $value = str_replace("]", "", $value);
        $value = str_replace("<", "", $value);
        $value = str_replace(">", "", $value);
        return trim($value);
    }
}