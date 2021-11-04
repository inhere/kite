<?php declare(strict_types=1);

namespace Inhere\Kite\Lib\Parser;

use Toolkit\Stdlib\Str;
use function explode;
use function is_array;
use function is_numeric;
use function is_string;
use function ltrim;
use function preg_match;
use function rtrim;
use function str_ends_with;
use function str_starts_with;
use function strlen;
use function substr;
use function trim;
use function vdump;

/**
 * class IniParser
 */
class IniParser
{
    /**
     * @param string $str
     *
     * @return array
     */
    public static function parseString(string $str): array
    {
        return (new self())->parse($str);
    }

    /**
     * parse ini string.
     *
     * - ignores commented lines that start with ";" or "#"
     * - ignores broken lines that do not have "="
     * - supports array values and array value keys
     * - enhance: supports inline array value
     *
     * @param string $str
     *
     * @return array
     * @url https://www.php.net/manual/en/function.parse-ini-string.php#111845
     */
    public function parse(string $str): array
    {
        if (!$str = trim($str)) {
            return [];
        }

        $ret   = [];
        $lines = explode("\n", $str);

        $sectionName = '';
        foreach ($lines as $line) {
            // empty line
            if (!$line = trim($line)) {
                continue;
            }

            // comments line
            if ($line[0] === "#" || $line[0] === ";" || str_starts_with($line, '//')) {
                continue;
            }

            // section line. eg: [arrayName]
            if (strlen($line) > 3 && $line[0] === '[' && str_ends_with($line, ']')) {
                $sectionName = substr($line, 1, -1);
                continue;
            }

            // invalid line
            if (!strpos($line, '=')) {
                continue;
            }

            $tmp = explode('=', $line, 2);
            $key = rtrim($tmp[0]);
            $val = ltrim($tmp[1]);

            // inline array value. eg: tags=[abc, 234]
            if ($val && $val[0] === '[' && str_ends_with($val, ']')) {
                $val = Str::toTypedArray(substr($val, 1,  - 1));
            }

            // top field
            if (!$sectionName) {
                $ret[$key] = $val;
                continue;
            }

            // in section. eg: [arrayName] -> $sectionName='arrayName'

            // remove quote chars
            if (
                is_string($val) &&
                (preg_match("/^\".*\"$/", $val) || preg_match("/^'.*'$/", $val))
            ) {
                $val = mb_substr($val, 1, -1);
            }

            // is array sub key.
            // eg:
            // [] = "arr_elem_one"
            // val_arr[] = "arr_elem_one"
            // val_arr_two[some_key] = "some_key_value"
            $ok = preg_match("/[\w-]{0,64}\[(.*?)]$/", $key, $matches);
            if ($ok === 1 && isset($matches[0])) {
                [$arrName, $subKey] = explode('[', trim($key, ']'));

                if ($arrName !== '') {
                    if (!isset($ret[$sectionName][$arrName]) || !is_array($ret[$sectionName][$arrName])) {
                        $ret[$sectionName][$arrName] = [];
                    }

                    if ($subKey !== '') { // eg: val_arr[subKey] = "arr_elem_one"
                        $ret[$sectionName][$arrName][$subKey] = $val;
                    } else { // eg: val_arr[] = "arr_elem_one"
                        $ret[$sectionName][$arrName][] = $val;
                    }
                } else {
                    if (!isset($ret[$sectionName]) || !is_array($ret[$sectionName])) {
                        $ret[$sectionName] = [];
                    }

                    if ($subKey !== '') { // eg: [subKey] = "arr_elem_one"
                        $ret[$sectionName][$subKey] = $val;
                    } else { // eg: [] = "arr_elem_one"
                        $ret[$sectionName][] = $val;
                    }
                }
            } else {
                $ret[$sectionName][$key] = $val;
            }
        }

        return $ret;
    }

    /**
     * @param string $str
     *
     * @return string
     */
    protected function removeQuotes(string $str): string
    {
        if (preg_match("/^\".*\"$/", $str) || preg_match("/^'.*'$/", $str)) {
            return mb_substr($str, 1, -1);
        }

        return $str;
    }

    /**
     * @param string $str
     *
     * @return array
     */
    protected function str2typedList(string $str): array
    {
        $str = substr($str, 1,  - 1);
        if (!$str) {
            return [];
        }

        $arr = Str::splitTrimFiltered($str);
        foreach ($arr as &$val) {
            if (is_numeric($val) && strlen($val) < 11) {
                $val = (int)$val;
            }
        }

        return $arr;
    }
}
