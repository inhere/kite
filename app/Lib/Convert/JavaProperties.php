<?php declare(strict_types=1);

namespace Inhere\Kite\Lib\Convert;

use Toolkit\Stdlib\Arr;
use Traversable;
use function array_shift;
use function count;
use function explode;
use function is_array;
use function is_int;
use function is_numeric;
use function is_scalar;
use function parse_ini_string;
use function str_contains;
use function trim;

/**
 * class JavaProperties
 */
class JavaProperties
{
    /**
     * @param string $str
     *
     * @return array
     */
    public function decode(string $str): array
    {
        $decoded = [];
        $rawData = (array)parse_ini_string($str, false);

        foreach ($rawData as $path => $value) {
            $path = trim($path, '.');
            if (str_contains($path, '.')) {
                $this->setNodeValue($decoded, $path, $value);
            } else {
                $decoded[$path] = $value;
            }
        }

        return $decoded;
    }

    /**
     * Set an array item to a given value using "dot" notation.
     * If no key is given to the method, the entire array will be replaced.
     *
     * @param array $array
     * @param string $path
     * @param mixed $value
     *
     * @return void
     */
    private function setNodeValue(array &$array, string $path, mixed $value): void
    {
        $keys = explode('.', $path);

        while (count($keys) > 1) {
            $key = array_shift($keys);
            $num = trim($key, '[]');
            if (is_numeric($num)) {
                $key = (int)$num;
            }

            // If the key doesn't exist at this depth, we will just create an empty array
            // to hold the next value, allowing us to create the arrays to hold final
            // values at the correct depth. Then we'll keep digging into the array.
            if (!isset($array[$key]) || !is_array($array[$key])) {
                $array[$key] = [];
            }

            $array = &$array[$key];
        }

        // last key
        $last = array_shift($keys);
        $num  = trim($last, '[]');
        if (is_numeric($num)) {
            $last = (int)$num;
        }

        $array[$last] = $value;
    }

    /**
     * @param iterable $data
     *
     * @return string
     */
    public function encode(iterable $data): string
    {
        $flatMap = [];
        $this->flattenData($data, $flatMap);

        return Arr::toKVString($flatMap);
    }

    /**
     * @param iterable $data
     * @param string $parentPath
     * @param array $flatMap
     */
    private function flattenData(iterable $data, array &$flatMap = [], string $parentPath = ''): void
    {
        foreach ($data as $key => $val) {
            if ($parentPath) {
                if (is_int($key)) {
                    $path = $parentPath . "[$key]";
                } else {
                    $path = $parentPath . '.' . $key;
                }
            } else {
                $path = $key;
            }

            if (is_scalar($val)) {
                $flatMap[$path] = $val;
            } else {
                $this->flattenData($val, $flatMap, $path);
            }
        }
    }
}
