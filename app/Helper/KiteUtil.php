<?php declare(strict_types=1);

namespace Inhere\Kite\Helper;

use Inhere\Kite\Kite;
use PhpPkg\EasyTpl\EasyTemplate;
use Toolkit\FsUtil\FS;
use Toolkit\Stdlib\Obj\DataObject;
use Toolkit\Stdlib\OS;
use Toolkit\Stdlib\Str;
use function array_merge;
use function defined;
use function dirname;
use function in_array;
use function is_file;
use function str_replace;
use const IN_PHAR;

/**
 * class KiteUtil
 */
class KiteUtil
{
    public const NL_CHAR    = 'NL';
    public const TAB_CHAR   = 'TAB';
    public const SPACE_CHAR = 'SPACE';

    public const STDIN_ALIAS = [
        '@i',
        '@si',
        '@stdin',
        'stdin',
    ];

    public const STDOUT_ALIAS = [
        '@o',
        '@so',
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
     * @return bool
     */
    public static function isInPhar(): bool
    {
        return defined('IN_PHAR') && IN_PHAR;
    }

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
     * @param array{tplDir: string, allowExt: array, globalVars: array} $config
     *
     * @return EasyTemplate
     */
    public static function newTplEngine(array $config = []): EasyTemplate
    {
        return EasyTemplate::textTemplate($config)
            ->setPathResolver([Kite::class, 'resolve'])
            ->configThis(function (EasyTemplate $tpl) {
                $tpl->tmpDir = Kite::getTmpPath('tplCache');
            })
            ->addFilters([
                'upFirst' => 'ucfirst',
                'camel'   => function (string $str, bool $upFirst = false): string {
                    return Str::toCamel($str, $upFirst);
                },
                'snake'   => function (string $str): string {
                    return Str::toSnake($str);
                },
                // prepend on not empty.
                'prepend' => function ($str, $char) {
                    return $str ? $char . $str : $str;
                },
                // append on not empty.
                'append'  => function ($str, $char) {
                    return $str ? $str . $char : $str;
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
            if (!trim($dir, '/')) {
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

    /**
     * command settings. id like: 'cmd:git:acp' 'cmd:gitlab:acp'
     *
     * @param string $commandId
     * @param string $parentKey
     *
     * @return DataObject
     */
    public static function getCmdConfig(string $commandId, string $parentKey = ''): DataObject
    {
        // command settings. id like: 'cmd:git:acp' 'cmd:gitlab:acp'
        $confKey = "cmd:$commandId";

        if ($parentKey) {
            $config = Kite::config()->getArray($parentKey);
            $config = array_merge($config, Kite::config()->getArray($confKey));
        } else {
            $config = Kite::config()->getArray($confKey);
        }

        return DataObject::new($config);
    }
}
