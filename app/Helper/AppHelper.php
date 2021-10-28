<?php declare(strict_types=1);

namespace Inhere\Kite\Helper;

use ArrayAccess;
use Inhere\Console\Util\Show;
use Inhere\Kite\Console\Component\Clipboard;
use Inhere\Kite\Kite;
use Toolkit\Cli\Cli;
use Toolkit\FsUtil\File;
use Toolkit\Stdlib\OS;
use Toolkit\Sys\Sys;
use function array_shift;
use function defined;
use function explode;
use function getenv;
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
     * @param string $pkgName 'inhere/console'
     *
     * @return bool
     */
    public static function isPhpPkgName(string $pkgName): bool
    {
        return true;
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
     * @param string $tag
     *
     * @return string
     */
    public static function formatTag(string $tag): string
    {
        $tag = trim($tag, 'v ');
        if (!$tag) {
            return '';
        }

        return 'v' . $tag;
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
            // $cmd = 'cmd /c start';
            $cmd = "start $pageUrl";
        } else {
            $cmd = "x-www-browser \"$pageUrl\"";
        }

        Show::info("Will open the page on browser:\n  $pageUrl");

        // Show::writeln("> $cmd");
        Sys::execute($cmd);
    }

    /**
     * @param string $path
     *
     * @return string eg: ~/.config/kite.php
     */
    public static function userConfigDir(string $path = ''): string
    {
        return OS::getUserHomeDir() . '/.config' . ($path ? "/$path" : '');
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
     * - input '@' or empty     - will read from Clipboard
     * - input '@i' or '@stdin' - will read from STDIN
     * - input '@FILEPATH'      - will read from the filepath.
     *
     * @param string $input the input text
     * @param string $loadedFile
     * @param array{print: bool} $opts
     *
     * @return string
     */
    public static function tryReadContents(string $input, string $loadedFile = '', array $opts = []): string
    {
        $print = $opts['print'] ?? true;

        $str = $input;
        if (!$input) {
            $str = Clipboard::new()->read();

            // is one line text
        } elseif (!str_contains($input, "\n") && str_starts_with($input, '@')) {
            if ($input === '@') {
                $print && Cli::info('try read contents from Clipboard');
                $str = Clipboard::new()->read();
            } elseif ($input === '@i' || $input === '@stdin') {
                $print &&  Cli::info('try read contents from STDIN');
                $str = Kite::cliApp()->getInput()->readAll();
                // $str = File::streamReadAll(STDIN);
                // $str = File::readAll('php://stdin');
                // vdump($str);
                // Cli::info('try read contents from STDOUT'); // error
                // $str = Kite::cliApp()->getOutput()->readAll();
            } elseif (($input === '@l' || $input === '@load') && is_file($loadedFile)) {
                $print && Cli::info('try read contents from file: ' . $loadedFile);
                $str = File::readAll($loadedFile);
            } else {
                $filepath = substr($input, 1);
                if (is_file($filepath)) {
                    $print && Cli::info('try read contents from file: ' . $filepath);
                    $str = File::readAll($filepath);
                }
            }
        }

        return $str;
    }
}
