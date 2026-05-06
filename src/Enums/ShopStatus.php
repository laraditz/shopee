<?php

namespace Laraditz\Shopee\Enums;

use Exception;

enum ShopStatus: int
{
    case Normal = 1;
    case Banned = 2;
    case Frozen = 3;

    public static function fromName(string $name)
    {
        foreach (self::cases() as $case) {
            if ($case->name === $name) {
                return $case;
            }
        }
        throw new Exception("$name is not a valid backing value for enum " . self::class);
    }

    public static function tryFromName(string $name)
    {
        try {
            return self::fromName($name);
        } catch (\Throwable $th) {
            return null;
        }
    }
}
