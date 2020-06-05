<?php

namespace RusBios\MediaHub\Utils;

use Exception;

class Dirs
{
    /**
     * @param int $id
     * @param string|null $basePAth
     * @param int $mode
     * @return string|null
     * @throws Exception
     */
    public static function getPath(int $id, string $basePAth = null, int $mode = 0644): ?string
    {
        $structure = str_split((string)$id, 3);

        $path = sprintf('%s/%s/', $basePAth, implode('/', $structure));

        if (!is_dir($path) && !mkdir($path, $mode, true)) {
            throw new Exception('unable to create directory');
        }

        return $path;
    }

    /**
     * @param int $bytes
     * @return string
     */
    public static function formatSize(int $bytes = null): string
    {
        if ($bytes) {
            $unit = ['b', 'Kb', 'Mb', 'Gb', 'Tb'];

            $size = $bytes / 8;

            foreach ($unit as $value) {
                if ($size < 1000) {
                    return round($size, 2) . ' ' . $value;
                }

                $size /= 1024;
            }
        }

        return '0 b';
    }
}
