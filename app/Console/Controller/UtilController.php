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
use Inhere\Console\Exception\PromptException;
use Inhere\Console\IO\Input;
use Inhere\Console\IO\Output;
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
            'tc'     => 'timeConv',
            'fjb'    => 'findJetBrains',
            'random' => ['rdm', 'rand'],
            'join'   => ['implode'],
        ];
    }

    /**
     * join multi line text
     *
     * @options
     *  --sep    The separator char. Defaults to an empty string.
     *
     * @param Input  $input
     * @param Output $output
     */
    public function joinCommand(Input $input, Output $output): void
    {
        $text = trim($input->getStringArg(0));
        if (!$text) {
            $output->colored('empty text');
            return;
        }

        $lines = explode("\n", $text);

        $sep = $input->getStringOpt('sep');
        echo implode($sep, $lines), "\n";
    }

    /**
     * print current datetime
     *
     * @param Input  $input
     * @param Output $output
     */
    public function dateCommand(Input $input, Output $output): void
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
     * generate an random string.
     *
     * @options
     *  -l, --length    The string length
     *  -t, --template  The sample template name. allow: alpha, alpha_num, alpha_num_c
     *
     * @param Input  $input
     * @param Output $output
     */
    public function randomCommand(Input $input, Output $output): void
    {
        $length  = $input->getSameIntOpt('l,length', 12);
        $samples = [
            'alpha'       => 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz',
            'alpha_num'   => '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz',
            'alpha_num_c' => '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz-+!@#$%&*',
        ];

        $sname = $input->getSameStringOpt('t,template', 'alpha_num');
        $chars = $samples[$sname] ?? $samples['alpha_num'];

        $str = '';
        $max = strlen($chars) - 1;   //strlen($chars) 计算字符串的长度

        for ($i = 0; $i < $length; $i++) {
            $str .= $chars[random_int(0, $max)];
        }

        $output->info('Generated: ' . $str);
    }

    /**
     * convert timestamp to datetime
     *
     * @param Input  $input
     * @param Output $output
     */
    public function timeConvCommand(Input $input, Output $output): void
    {
        $args = $input->getArguments();
        if (!$args) {
            throw new PromptException('missing arguments');
        }

        $data = [];
        foreach ($args as $time) {
            if (strlen($time) > 10) {
                $time = substr($time, 0, 10);
            }

            $data[] = [
                'timestamp' => $time,
                'datetime'  => date('Y-m-d H:i:s', (int)$time),
            ];
        }

        $output->colored('- Current Time: ' . date('Y-m-d H:i:s'));
        $output->table($data, 'Time to date', [// opts
        ]);
    }

    /**
     * find IDEA in the machine
     *
     * @param Input  $input
     * @param Output $output
     */
    public function findJetBrainsCommand(Input $input, Output $output): void
    {
        $dirs = [
            // '~/Library/Preferences/PhpStorm2019.3/',
            '~/Library/Application\ Support/JetBrains/',
            '~/Library/Application\ Support/JetBrains/GoLand2020.1/eval',
            '~/Library/Application\ Support/JetBrains/Toolbox/apps/',
        ];

        $ideName = $input->getStringArg('name', 'all');
        // rm -rf ~/Library/Application\ Support/${NAME}*/eval

        vdump($dirs);
    }
}
