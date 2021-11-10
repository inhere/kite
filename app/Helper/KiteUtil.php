<?php declare(strict_types=1);

namespace Inhere\Kite\Helper;

use Inhere\Kite\Kite;
use Inhere\Kite\Lib\Template\EasyTemplate;
use Inhere\Kite\Lib\Template\TextTemplate;
use Toolkit\FsUtil\FS;
use Toolkit\Stdlib\OS;
use Toolkit\Stdlib\Str;
use function dirname;
use function in_array;
use function is_file;
use function str_replace;

/**
 * class KiteUtil
 */
class KiteUtil
{

    public const NL_CHAR    = 'NL';
    public const SPACE_CHAR = 'SPACE';

    public const STDIN_ALIAS = [
        '@i',
        '@stdin',
        'stdin',
    ];

    public const STDOUT_ALIAS = [
        '@o',
        '@stdout',
        'stdout',
    ];

    public const CLIPBOARD_ALIAS = [
        '@c',
        '@cb',
        '@clip',
        '@clipboard',
        'clipboard',
    ];

    /**
     * @param string $sep
     *
     * @return string
     */
    public static function resolveSep(string $sep): string
    {
        return str_replace([self::NL_CHAR, self::SPACE_CHAR], ["\n", ' '], $sep);
    }

    /**
     * @param string $str
     *
     * @return bool
     */
    public static function isStdinAlias(string $str): bool
    {
        return in_array($str, self::STDIN_ALIAS, true);
    }

    /**
     * @param string $str
     *
     * @return bool
     */
    public static function isStdoutAlias(string $str): bool
    {
        return in_array($str, self::STDOUT_ALIAS, true);
    }

    /**
     * @param string $str
     *
     * @return bool
     */
    public static function isClipboardAlias(string $str): bool
    {
        return in_array($str, self::CLIPBOARD_ALIAS, true);
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
     * @param array $config
     *
     * @return EasyTemplate
     */
    public static function newTplEngine(array $config = []): EasyTemplate
    {
        return TextTemplate::new($config)
            ->setPathResolver([Kite::class, 'resolve'])
            ->configThis(function (EasyTemplate $tpl) {
                $tpl->tmpDir = Kite::getTmpPath('tplCache');
            })
            ->addFilters([
                'upFirst' => 'ucfirst',
                'upper'   => 'strtoupper',
                'nl'      => function (string $str): string {
                    return $str . "\n";
                },
                'camel'   => function (string $str, bool $upFirst): string {
                    return Str::toCamel($str, $upFirst);
                },
                'snake'   => function (string $str): string {
                    return Str::toSnake($str);
                },
            ]);
    }

    /**
     * @param string $dir
     *
     * @return string
     */
    public static function findPhpUnitConfigFile(string $dir): string
    {
        $dir = FS::realpath($dir);

        while (true) {
            if (!$dir) {
                break;
            }

            if (
                is_file($dir . '/phpunit.xml') ||
                is_file($dir . '/phpunit.xml.dist')
            ) {
                break;
            }

            $dir = dirname($dir);
        }

        return $dir;
    }
}
