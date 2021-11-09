<?php declare(strict_types=1);
/**
 * This file is part of Kite.
 *
 * @link     https://github.com/inhere
 * @author   https://github.com/inhere
 * @license  MIT
 */

namespace Inhere\Kite\Console\Controller;

use Cron\CronExpression;
use Exception;
use HuCron\HuCron;
use Inhere\Console\Controller;
use Inhere\Console\IO\Input;
use Inhere\Console\IO\Output;
use Toolkit\PFlag\FlagsParser;

/**
 * Class CronTabController
 */
class CronTabController extends Controller
{
    protected static $name = 'crontab';

    protected static $desc = 'parse or convert crontab expression';

    public static function aliases(): array
    {
        return ['cron'];
    }

    protected static function commandAliases(): array
    {
        return [
            'execTime' => ['exec-time', 'execat', 'runat', 'next']
        ];
    }

    /**
     * parse the human readable statement to an cron expression.
     *
     * @arguments
     *  statement       string;The human readable statement;required
     *
     * @options
     *  -i          bool;Run an interactive environment
     *
     * @param FlagsParser $fs
     * @param Output $output
     *
     * @throws Exception
     * @example
     *  {binWithCmd} 'every 6 mins'         // '*\/6 * * * *'
     *  {binWithCmd} 'every day 10 am'      // '0 10 * * *'
     *  {binWithCmd} 'every day 10:20 am'   // '20 10 * * *'
     *
     */
    public function parseCommand(FlagsParser $fs, Output $output): void
    {
        $stat = $fs->getArg('statement');
        $output->colored("Input Statement: '$stat'");

        $expr = HuCron::fromStatement($stat);
        $output->writeln("Cron Expression: <suc>$expr</suc>");

        $dateList = $this->getNextNTimesRunDate($nextNum = 3, $expr);
        $output->aList($dateList, "Execution time for the next $nextNum times:");
    }

    /**
     * show next execution datetime for an cron expression.
     *
     * @arguments
     *  expression      string;The cronTab expression. eg: `20 10 * * *`;required
     *
     * @options
     *  -n, --next      int;Show next number exec datetime. default number is 3.
     *  -p, --prev      bool;Show previsions exec datetime
     *
     * @param FlagsParser $fs
     * @param Output $output
     *
     * @throws Exception
     */
    public function execTimeCommand(FlagsParser $fs, Output $output): void
    {
        $expr = $fs->getArg('expression');
        $output->colored('Cron Expression: ' . $expr);

        $nextNum  = $fs->getOpt('next', 3);
        $dateList = $this->getNextNTimesRunDate($nextNum, $expr);

        $output->aList($dateList, "Execution time for the next $nextNum times:");
    }

    /**
     * @param int    $nextNum
     * @param string $expr
     *
     * @return array
     * @throws Exception
     */
    private function getNextNTimesRunDate(int $nextNum, string $expr): array
    {
        $cronExpr = new CronExpression($expr);

        $dateList = [];
        for ($i=0; $i < $nextNum; $i++) {
            $date = $cronExpr->getNextRunDate('now', $i);

            $dateList[] = $date->format('Y-m-d H:i:s');
        }

        return $dateList;
    }
}
