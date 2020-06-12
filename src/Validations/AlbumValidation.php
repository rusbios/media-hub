<?php

namespace MediaHub\Validations;

use Illuminate\Http\Request;

class AlbumValidation implements ValidInterface
{
    public static function isValid(Request $request): bool
    {
        $data = $request->all(['name']);

        return !empty($data['name']);
    }

    /**
     * @param Request $request
     * @return array|null
     */
    public static function getValidData(Request $request)
    {
        return static::isValid($request) ? $request->all(['name', 'access']) : null;
    }
}
