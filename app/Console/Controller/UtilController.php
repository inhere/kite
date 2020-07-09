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

/**
 * Class UtilController
 */
class UtilController extends Controller
{
    protected static $name = 'util';

    protected static $description = 'Some useful development tool commands';

    protected static function commandAliases(): array
    {
        return ['tc' => 'timeConv'];
    }

    /**
     * print current datetime
     *
     * @param Input  $input
     * @param Output $output
     */
    public function dateCommand(Input $input, Output $output): void
    {
        $output->println('Time: ' . date('Y-m-d H:i:s'));
        // $output->success('Complete');
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
            $data[] = [
                'timestamp' => $time,
                'datetime' => date('Y-m-d H:i:s', (int)$time),
            ];
        }

        $output->colored('- Current Time: ' . date('Y-m-d H:i:s'));
        $output->table($data, 'Time to date', [
            // opts
        ]);
    }
}