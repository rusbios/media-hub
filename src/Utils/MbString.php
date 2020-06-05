<?php
namespace RusBios\MediaHub\Utils;

use Exception;
use SimpleXMLElement;

/**
 * Class MbString
 */
class MbString
{
    private const CONVERTER = [
        'а' => 'a',
        'б' => 'b',
        'в' => 'v',
        'г' => 'g',
        'д' => 'd',
        'е' => 'e',
        'ё' => 'e',
        'ж' => 'zh',
        'з' => 'z',
        'и' => 'i',
        'й' => 'y',
        'к' => 'k',
        'л' => 'l',
        'м' => 'm',
        'н' => 'n',
        'о' => 'o',
        'п' => 'p',
        'р' => 'r',
        'с' => 's',
        'т' => 't',
        'у' => 'u',
        'ф' => 'f',
        'х' => 'h',
        'ц' => 'c',
        'ч' => 'ch',
        'ш' => 'sh',
        'щ' => 'sch',
        'ь' => '\'',
        'ы' => 'y',
        'ъ' => '\'',
        'э' => 'e',
        'ю' => 'yu',
        'я' => 'ya',
        'А' => 'A',
        'Б' => 'B',
        'В' => 'V',
        'Г' => 'G',
        'Д' => 'D',
        'Е' => 'E',
        'Ё' => 'E',
        'Ж' => 'Zh',
        'З' => 'Z',
        'И' => 'I',
        'Й' => 'Y',
        'К' => 'K',
        'Л' => 'L',
        'М' => 'M',
        'Н' => 'N',
        'О' => 'O',
        'П' => 'P',
        'Р' => 'R',
        'С' => 'S',
        'Т' => 'T',
        'У' => 'U',
        'Ф' => 'F',
        'Х' => 'H',
        'Ц' => 'C',
        'Ч' => 'Ch',
        'Ш' => 'Sh',
        'Щ' => 'Sch',
        'Ь' => '\'',
        'Ы' => 'Y',
        'Ъ' => '\'',
        'Э' => 'E',
        'Ю' => 'Yu',
        'Я' => 'Ya',
    ];

    private const CHARACTERS_SAFE = '23456789ABCDEFGHJKMNPQRSTUVWXYZ';

    private const CHARACTERS_STR = '1234567890QWERTYUIOPLKJHGFDSAZXCVBNM';

    private const CHARACTERS_INT = '0123456789';

    /**
     * @param string $string
     *
     * @return string
     */
    public static function removeNonUtf8Chars(string $string): string
    {
        return mb_convert_encoding($string, 'UTF-8', 'UTF-8');
    }

    /**
     * @param string $string
     *
     * @return string
     */
    public static function removeBOM(string $string): string
    {
        return preg_replace('/\x{FEFF}/u', '', $string);
    }

    /**
     * Если строка не преобразовалась, то вернем пустой массив
     *
     * @param string $xmlString
     *
     * @return array
     */
    public static function parseXmlStringToArray($xmlString): ?array
    {
        $masc = LIBXML_NOERROR | LIBXML_NOWARNING | LIBXML_ERR_NONE | LIBXML_NOCDATA;
        $xml = simplexml_load_string($xmlString, 'SimpleXMLElement', $masc);

        if (!($xml instanceof SimpleXMLElement)) {
            return [];
        }

        $result = json_decode(json_encode($xml), true);

        return is_array($result) ? $result : [];
    }

    /**
     * @param int $length
     * @param string|null $symbols
     *
     * @return null|string
     * @throws Exception
     */
    public static function generateSymbols(int $length, string $symbols = null): ?string
    {
        $result = '';
        $symbols = $symbols ?? self::CHARACTERS_SAFE;

        $random = random_bytes($length);
        $charactersLength = mb_strlen($symbols);
        for ($i = 0; $i < $length; $i++) {
            $result .= $symbols[ord($random[$i]) % $charactersLength];
        }

        return $result;
    }

    /**
     * @param int $length
     *
     * @return string
     * @throws Exception
     */
    public static function generateRandomNumber(int $length = 6): string
    {
        return self::generateSymbols($length, self::CHARACTERS_INT);
    }

    /**
     * @param string $contact
     * @param int $first
     * @param int $end
     * @return string
     */
    public static function maskContact(string $contact, int $first = 3, int $end = 3): string
    {
        $result = substr($contact, 0, $first);

        $len = strlen(substr($contact, $first, -$end));
        while (--$len) {
            $result .= '*';
        }
        $result .= substr($contact, -$end, $end);

        return $result;
    }

    /**
     * @param string|null $ip
     * @return string
     * @throws Exception
     */
    public static function makeGUID(?string $ip): string
    {
        if (function_exists('com_create_guid')) {
            return trim(com_create_guid(), '{}');
        }

        $data = [];

        while (count($data) < 8) {
            $data[] = static::generateSymbols(4, static::CHARACTERS_STR);
            if ($ip && count($data) == 6) {
                $crypt = strtoupper(self::cryptIp($ip));
                $data[] = substr($crypt, 0, 4);
                $data[] = substr($crypt, 4);
            }
        }

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', $data);
    }

    /**
     * @param string|null $str
     * @return string|null
     */
    public static function transLitRus(?string $str): ?string
    {
        if (!$str) {
            return null;
        }
        return strtr($str, self::CONVERTER);
    }

    /**
     * @param string|null $str
     * @return string|null
     */
    public static function toUrl(?string $str): ?string
    {
        if (!$str) {
            return null;
        }
        $str = strtolower(self::transLitRus(trim($str)));
        return preg_replace('~[^-a-z0-9_]+~u', '-', $str);
    }

    /**
     * @param string $ip
     * @return string
     */
    public static function cryptIp(string $ip): string
    {
        return dechex(ip2long($ip));
    }

    /**
     * @param string $crypt
     * @return string
     */
    public static function decryptIp(string $crypt): string
    {
        return long2ip(hexdec($crypt));
    }
}
