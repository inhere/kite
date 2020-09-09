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
use function strlen;
use function strtotime;
use function substr;
use function time;

/**
 * Class UtilController
 */
class UtilController extends Controller
{
    public const JB_NAME = 'JetBrains';
    public const JB_TBOX = 'Toolbox';
    public const JB_ALL = [
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
            'tc'  => 'timeConv',
            'fjb' => 'findJetBrains',
        ];
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
        $oneDayAgo = strtotime('-1 day',  $time);
        $oneDayLater = strtotime('+1 day',  $time);

        $output->aList([
            'current time' => $time,
            'current date' => date('Y-m-d H:i:s', $time),
            'one day ago' => $oneDayAgo,
            'one day ago date' => date('Y-m-d H:i:s', $oneDayAgo),
            'one day later' => $oneDayLater,
            'one day later date' => date('Y-m-d H:i:s', $oneDayLater),
            'yesterday start' => date('Y-m-d 00:00:01', $time),
        ], 'recently date');
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

        var_dump($dirs);
    }
}
