<?php declare(strict_types=1);
/**
 * This file is part of Kite.
 *
 * @link     https://github.com/inhere
 * @author   https://github.com/inhere
 * @license  MIT
 */

namespace Inhere\Kite\Console\Controller;

use Inhere\Console\Controller;
use Inhere\Console\IO\Output;
use Inhere\Kite\Console\Component\Clipboard;
use Inhere\Kite\Kite;
use Toolkit\Cli\Color;
use Toolkit\PFlag\FlagsParser;
use function date;
use function printf;
use function strtotime;
use function time;

/**
 * Class UtilController
 */
class UtilController extends Controller
{
    public const JB_NAME = 'JetBrains';
    public const JB_TBOX = 'Toolbox';
    public const JB_ALL  = [
        'IntelliJIdea',
        'CLion',
        'PhpStorm',
        'GoLand',
        'PyCharm',
        'WebStorm',
        'Rider',
        'DataGrip',
        'RubyMine',
        'AppCode',
    ];

    public const JB_NORMAL = [
        'IntelliJIdea',
        'CLion',
        'PhpStorm',
        'GoLand',
    ];

    protected static string $name = 'util';

    protected static string $desc = 'Some useful development tool commands';

    protected static function commandAliases(): array
    {
        return [
            'fjb'       => 'findJetBrains',
            'clipboard' => [
                'clip',
                'cb'
            ],
        ];
    }

    /**
     * print current datetime
     *
     * @param Output $output
     */
    public function dateCommand(Output $output): void
    {
        $time = time();

        $oneDayAgo   = strtotime('-1 day', $time);
        $oneDayLater = strtotime('+1 day', $time);

        $curDay = date('Y-m-d', $time);
        $output->aList([
            'today'                => $curDay,
            'current time'         => $time,
            'current date'         => date('Y-m-d H:i:s', $time),
            'start time(00:00:01)' => strtotime("$curDay 00:00:01", $time),
            'end time(23:59:59)'   => strtotime("$curDay 23:59:59", $time),
        ], 'today');

        $output->aList([
            'one day ago'        => $oneDayAgo,
            'one day ago date'   => date('Y-m-d H:i:s', $oneDayAgo),
            'one day later'      => $oneDayLater,
            'one day later date' => date('Y-m-d H:i:s', $oneDayLater),
            'yesterday start'    => date('Y-m-d 00:00:01', $time),
        ], 'recently date');
    }

    /**
     * find IDEA in the machine
     *
     * @arguments
     * name      ide name, such as: phpStorm
     *
     * @param FlagsParser $fs
     * @param Output $output
     */
    public function findJetBrainsCommand(FlagsParser $fs, Output $output): void
    {
        // doc: https://www.jetbrains.com/help/idea/directories-used-by-the-ide-to-store-settings-caches-plugins-and-logs.html
        $dirs = [
            // '~/Library/Preferences/PhpStorm2019.3/',
            '~/Library/Application\ Support/JetBrains/',
            // '~/Library/Application\ Support/JetBrains/GoLand2020.1/eval',
            '~/Library/Application\ Support/JetBrains/Toolbox/apps/',
            'logs' => '~/Library/Logs/JetBrains/',
            'cache' => ' ~/Library/Caches/JetBrains/',
        ];

        $ideName = $fs->getArg('name', 'all');
        // rm -rf ~/Library/Application\ Support/${NAME}*/eval
        $output->aList($dirs, $ideName);
    }

    /**
     * @options
     * -r, --read       bool;read and show clipboard contents.
     * -w, --write      write input contents to clipboard.
     *
     * @param FlagsParser $fs
     * @param Output $output
     */
    public function clipboardCommand(FlagsParser $fs, Output $output): void
    {
        if ($fs->getOpt('read')) {
            echo Clipboard::readAll();
            return;
        }

        $str = $fs->getMustOpt('write');
        if ($str === '@i' || $str === '@stdin') {
            $str = $this->input->readAll();
        }

        $ok  = Clipboard::writeString($str);

        if ($ok) {
            $output->success('contents sent to clipboard');
        } else {
            $output->error('contents send fail to clipboard');
        }
    }

    /**
     * @param Output $output
     */
    public function colorCommand(Output $output): void
    {
        $output->colored('All color tags:');

        foreach (Color::getStyles() as $style) {
            printf("    %s: %s\n", $style, Color::apply($style, 'This is a message'));
        }
    }

    /**
     * @arguments
     * msg      The log message
     *
     * @options
     * --type       The log type name.
     *
     * @param FlagsParser $fs
     */
    public function logCommand(FlagsParser $fs): void
    {
        $msg  = $fs->getFirstArg();
        $type = $fs->getOpt('type');

        if (!$msg && !$type) {
            return;
        }

        Kite::logger()->info($msg, ['type' => $type]);
    }
}
