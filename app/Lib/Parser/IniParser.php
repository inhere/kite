<?php declare(strict_types=1);

namespace Inhere\Kite\Lib\Parser;

use Toolkit\Stdlib\Str;
use function explode;
use function is_numeric;
use function ltrim;
use function preg_match;
use function rtrim;
use function str_ends_with;
use function strlen;
use function substr;
use function trim;

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
     * - supports inline array value
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
            $line = trim($line);

            // empty or comments line
            if (!$line || $line[0] === "#" || $line[0] === ";") {
                continue;
            }

            // section line. eg: [arrayName]
            if ($line[0] === '[' && $endIdx = strpos($line, ']')) {
                $sectionName = substr($line, 1, $endIdx - 1);
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
                $val = Str::splitTrimmed(substr($val, 1,  - 1));
            }

            // in section. eg: [arrayName] -> $sectionName='arrayName'
            if ($sectionName) {
                // remove quote chars
                if (preg_match("/^\".*\"$/", $val) || preg_match("/^'.*'$/", $val)) {
                    $val = mb_substr($val, 1, -1);
                }

                // is array.
                // eg: val_arr[] = "arr_elem_one"
                // eg: val_arr_two[some_key] = "some_key_value"
                // $t = preg_match("^\[(.*?)\]^", $key, $matches);
                $ok = preg_match("^\[(.*?)]^", $key, $matches);
                if ($ok === 1 && isset($matches[0])) {
                    // $arr_name = preg_replace('#\[(.*?)\]#is', '', $key);
                    [$arrName, $subKey] = explode('[', trim($key, ']'));

                    if (!isset($ret[$sectionName][$arrName]) || !is_array($ret[$sectionName][$arrName])) {
                        $ret[$sectionName][$arrName] = [];
                    }

                    if ($subKey !== '') {
                        $ret[$sectionName][$arrName][$subKey] = $val;
                    } else { // eg: val_arr[] = "arr_elem_one"
                        $ret[$sectionName][$arrName][] = $val;
                    }
                } else {
                    $ret[$sectionName][$key] = $val;
                }
            } else {
                $ret[$key] = $val;
            }
        }

        return $ret;
    }

    protected function removeQuotes(string $str): string
    {
        if (preg_match("/^\".*\"$/", $str) || preg_match("/^'.*'$/", $str)) {
            return mb_substr($str, 1, -1);
        }

        return $str;
    }

    protected function str2array(string $str): array
    {
        if (!$str) {
            return [];
        }

        $arr = Str::splitTrimmed(substr($str, 1,  - 1));
        foreach ($arr as $val) {
            if (is_numeric($val) && strlen($val) < 11) {

            }
        }

        return $arr;
    }
}
