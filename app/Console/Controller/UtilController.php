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
use Inhere\Kite\Kite;
use Toolkit\PFlag\FlagsParser;
use function date;
use function explode;
use function implode;
use function strlen;
use function strtotime;
use function substr;
use function time;
use function trim;

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

    protected static $name = 'util';

    protected static $description = 'Some useful development tool commands';

    protected static function commandAliases(): array
    {
        return [
            'fjb'  => 'findJetBrains',
            'join' => ['implode'],
        ];
    }

    /**
     * join multi line text
     *
     * @arguments
     * text     The multi line text
     *
     * @options
     *  --sep    The separator char. Defaults to an empty string.
     *
     * @param FlagsParser $fs
     * @param Output $output
     */
    public function joinCommand(FlagsParser $fs, Output $output): void
    {
        $text = trim($fs->getArg(0));
        if (!$text) {
            $output->colored('empty text');
            return;
        }

        $lines = explode("\n", $text);

        $sep = $fs->getOpt('sep');
        echo implode($sep, $lines), "\n";
    }

    /**
     * print current datetime
     *
     * @param Output $output
     */
    public function dateCommand( Output $output): void
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
     * @param FlagsParser $fs
     * @param Output $output
     */
    public function findJetBrainsCommand(FlagsParser $fs, Output $output): void
    {
        $dirs = [
            // '~/Library/Preferences/PhpStorm2019.3/',
            '~/Library/Application\ Support/JetBrains/',
            '~/Library/Application\ Support/JetBrains/GoLand2020.1/eval',
            '~/Library/Application\ Support/JetBrains/Toolbox/apps/',
        ];

        $ideName = $fs->getArg('name', 'all');
        // rm -rf ~/Library/Application\ Support/${NAME}*/eval

        vdump($dirs);
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
        $msg = $fs->getFirstArg();
        $type = $fs->getOpt('type');

        if (!$msg && !$type) {
            return;
        }

        Kite::logger()->info($msg, ['type' => $type]);
    }
}
