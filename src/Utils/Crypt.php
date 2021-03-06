<?php

namespace MediaHub\Utils;

use Illuminate\Support\Facades\Crypt as FCrypt;

class Crypt
{
    /**
     * @param string $value
     * @return string
     */
    public static function encryptString(string $value): string
    {
        return FCrypt::encryptString($value);
    }

    /**
     * @param string $value
     * @return string
     */
    public static function decryptString(string $value): string
    {
        return FCrypt::decryptString($value);
    }

    /**
     * @param array $data
     * @return string
     */
    public static function encryptArray(array $data): string
    {
        return static::encryptString(json_encode($data));
    }

    /**
     * @param string $value
     * @return array
     */
    public static function decryptArray(string $value): array
    {
        return json_decode(static::decryptString($value), true);
    }
}
