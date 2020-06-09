<?php

namespace MediaHub\Utils;

class Bits
{
    /**
     * Проверим есть ли бит в битовой маске
     *
     * @param int       $mask
     * @param int|int[] $bits
     *
     * @return bool|bool[]
     */
    public static function has($mask, $bits)
    {
        $return = [];

        foreach ((array)$bits as $bit) {
            $return[$bit] = ($mask & (1 << (int)$bit)) > 0;
        }

        return is_array($bits) ? $return : array_shift($return);
    }

    /**
     * Добавим бит в битовую маску
     *
     * @param int       $mask
     * @param int|int[] $bits
     *
     * @return int
     */
    public static function add($mask, $bits)
    {
        foreach ((array)$bits as $bit) {
            $mask |= (1 << (int)$bit);
        }

        return $mask;
    }

    /**
     * Удалим бит из битовой маски
     *
     * @param int       $mask
     * @param int|int[] $bits
     *
     * @return int
     */
    public static function delete($mask, $bits)
    {

        foreach ((array)$bits as $bit) {
            $mask &= ~(1 << (int)$bit);
        }

        return $mask;
    }

    /**
     * Посчитаем количество битов
     *
     * @param int $mask
     *
     * @return int
     */
    public static function count($mask): int
    {
        $c = (int)$mask - (((int)$mask >> 1) & 0x55555555);
        $c = (($c >> 2) & 0x33333333) + ($c & 0x33333333);
        $c = (($c >> 4) + $c) & 0x0F0F0F0F;
        $c = (($c >> 8) + $c) & 0x00FF00FF;
        $c = (($c >> 16) + $c) & 0x0000FFFF;

        return $c;
    }
}
