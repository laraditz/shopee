<?php

namespace Laraditz\Shopee\Enums;

class Enums
{
    public static function getValue($var)
    {
        $oClass = new \ReflectionClass(get_called_class());
        $constant = $oClass->getConstants();

        return data_get($constant, $var);
    }
}
