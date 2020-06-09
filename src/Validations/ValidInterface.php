<?php

namespace MediaHub\Validations;

use Illuminate\Http\Request;

interface ValidInterface
{
    /**
     * @param Request $request
     * @return bool
     */
    public static function isValid(Request $request): bool;

    /**
     * @param Request $request
     * @return array|null
     */
    public static function getValidData(Request $request);
}
