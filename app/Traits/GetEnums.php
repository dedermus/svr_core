<?php

namespace App\Traits;

trait GetEnums
{
    /**
     * Get value list enum
     *
     * @return array
     */
    public static function get_value_list(): array
    {
        return array_column(self::cases(), 'value');

    }

    /**
     * Get name list enum
     *
     * @return array
     */
    public static function get_name_list(): array
    {
        return array_column(self::cases(), 'name');
    }

    public static function get_option_list(): array
    {
        return array_combine(self::get_value_list(), self::get_value_list());
    }

    /**
     * Get array enum
     *
     * @return array
     */
    public static function get_array(): array
    {
        $array_objects = self::cases();
        return array_combine(array_column($array_objects, 'value'), array_column($array_objects, 'name'));
    }

    /**
     * Get a string from a list enum
     *
     * @return string
     */
    public static function get_value_str(): string
    {
        return implode(
            ", ", array_map(function ($v) {
            return "'$v'";
        }, array_column(self::cases(), 'value'))
        );
    }
}
