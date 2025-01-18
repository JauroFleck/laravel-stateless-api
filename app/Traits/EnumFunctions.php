<?php

namespace App\Traits;

trait EnumFunctions
{
    public static function casesToString(): array
    {
        return array_map(function ($element) {
            return $element->name;
        }, static::cases());
    }
}
