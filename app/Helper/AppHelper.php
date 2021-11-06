<?php declare(strict_types=1);

namespace Inhere\Kite\Helper;

use ArrayAccess;
use Closure;
use Inhere\Console\Util\Show;
use Inhere\Kite\Console\Component\Clipboard;
use Inhere\Kite\Console\Component\ContentsAutoReader;
use Inhere\Kite\Kite;
use Toolkit\Cli\Cli;
use Toolkit\FsUtil\File;
use Toolkit\Stdlib\OS;
use Toolkit\Sys\Sys;
use function array_filter;
use function array_shift;
use function array_slice;
use function count;
use function defined;
use function explode;
use function getenv;
use function implode;
use function is_array;
use function is_file;
use function is_object;
use function random_int;
use function strlen;
use function strpos;
use function substr;
use function trim;
use function vdump;
use const STDIN;

/**
 * Class AppHelper
 *
 * @package Inhere\Kite\Helper
 */
class AppHelper
{
    public const LANG_MAP = [
        'zh_CN' => 'zh-CN',
    ];

    /**
     * @param string $version eg: 2.0.8, v2.0.8.1 v4.0.0beta1
     *
     * @return bool
     */
    public static function isVersion(string $version): bool
    {
        return 1 === preg_match('#^v?\d{1,2}.\d{1,2}.\d{1,3}[.\w]*$#', $version);
    }

    /**
     * @return bool
     */
    public static function isInPhar(): bool
    {
        if (defined('IN_PHAR')) {
            return IN_PHAR;
        }
        return false;
    }

    /**
     * env: LC_CTYPE=zh_CN.UTF-8
     *
     * @param string $default
     *
     * @return string
     */
    public static function getLangFromENV(string $default = ''): string
    {
        $value = (string)getenv('LC_CTYPE');

        // zh_CN.UTF-8
        if (strpos($value, '.') > 0) {
            [$value,] = explode('.', $value);

            return self::LANG_MAP[$value] ?? $value;
        }

        return $default;
    }

    /**
     * Open browser URL
     *
     * Mac：
     * open 'https://swoft.org'
     *
     * Linux:
     * x-www-browser 'https://swoft.org'
     *
     * Windows:
     * cmd /c start https://swoft.org
     *
     * @param string $pageUrl
     */
    public static function openBrowser(string $pageUrl): void
    {
        if (Sys::isMac()) {
            $cmd = "open \"$pageUrl\"";
        } elseif (Sys::isWin()) {
            // $cmd = 'cmd /c start URL';
            $cmd = "start $pageUrl";
        } else {
            // $cmd = 'xdg-open URL';
            $cmd = "x-www-browser \"$pageUrl\"";
        }

        Show::info("Will open the page on browser:\n  $pageUrl");

        // Show::writeln("> $cmd");
        Sys::execute($cmd);
    }

    /**
     * @param string $sname
     * @param int    $length
     *
     * @return string
     * @throws \Exception
     */
    public static function genRandomStr(string $sname, int $length): string
    {
        $samples = [
            'alpha'        => 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz',
            'alpha_num'    => '0123456789abcdefghijklmnopqrstuvwxyz',
            'alpha_num_up' => '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz',
            'alpha_num_c'  => '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz-+!@#$%&*',
        ];

        $chars = $samples[$sname] ?? $samples['alpha_num'];

        $str = '';
        $max = strlen($chars) - 1;

        for ($i = 0; $i < $length; $i++) {
            $str .= $chars[random_int(0, $max)];
        }

        return $str;
    }

    /**
     * Get data from array or object by path.
     * Example: `DataCollector::getByPath($array, 'foo.bar.yoo')` equals to $array['foo']['bar']['yoo'].
     *
     * @param array|ArrayAccess $data      An array or object to get value.
     * @param mixed             $path      The key path.
     * @param mixed             $default
     * @param string            $separator Separator of paths.
     *
     * @return mixed Found value, null if not exists.
     */
    public static function getByPath($data, string $path, $default = null, string $separator = '.')
    {
        if (isset($data[$path])) {
            return $data[$path];
        }

        // Error: will clear '0'. eg 'some-key.0'
        // if (!$nodes = array_filter(explode($separator, $path))) {
        if (!$nodes = explode($separator, $path)) {
            return $default;
        }

        if ($nodes[0] === '$') {
            array_shift($nodes);
        }

        if ($nodes[0] === '*') {
            array_shift($nodes);
            $dataTmp = [];
            foreach ($data as $item) {
                $dataTmp[] = self::getValueByNodes($item, $nodes);
            }
            return $dataTmp;
        }

        $dataTmp = $data;
        foreach ($nodes as $key) {
            // data must an array list.
            if ($key === '*' && isset($data[0])) {
                $dataTmp = [];
                foreach ($data as $item) {
                    $dataTmp[] = self::getByPath($item, $key);
                }
                continue;
            }

            if (is_object($dataTmp) && isset($dataTmp->$key)) {
                $dataTmp = $dataTmp->$key;
            } elseif ((is_array($dataTmp) || $dataTmp instanceof ArrayAccess) && isset($dataTmp[$key])) {
                $dataTmp = $dataTmp[$key];
            } else {
                return $default;
            }
        }

        return $dataTmp;
    }

    /**
     * findValueByNodes
     *
     * @param array $data
     * @param array $nodes
     * @param mixed $default
     *
     * @return mixed
     */
    public static function getValueByNodes(array $data, array $nodes, $default = null)
    {
        $temp = $data;
        foreach ($nodes as $name) {
            if (isset($temp[$name])) {
                $temp = $temp[$name];
            } else {
                $temp = $default;
                break;
            }
        }

        return $temp;
    }

    /**
     * try read contents
     *
     * - input empty or '@i' or '@stdin'     - will read from STDIN
     * - input '@c' or '@cb' or '@clipboard' - will read from Clipboard
     * - input '@l' or '@load'               - will read from loaded file
     * - input '@FILEPATH'                   - will read from the filepath.
     *
     * @param string $input the input text
     * @param array{print: bool, loadedFile: string} $opts
     *
     * @return string
     */
    public static function tryReadContents(string $input, array $opts = []): string
    {
        return ContentsAutoReader::readFrom($input, $opts);
    }

    /**
     * @return Closure
     */
    public static function json5lineParser(): Closure
    {
        return static function (string $line, int $fieldNum) {
            $nodes = array_filter(explode(' ', $line), 'strlen');
            $count = count($nodes);
            if ($count <= $fieldNum) {
                return $nodes;
            }

            $values = array_slice($nodes, 0, $fieldNum - 1);
            $others = array_slice($nodes, $fieldNum - 1);

            // merge others as last elem
            $values[] = implode(' ', $others);
            return $values;
        };
    }
}
