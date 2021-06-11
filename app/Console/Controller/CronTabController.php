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

/**
 * Class CronTabController
 */
class CronTabController extends Controller
{
    protected static $name = 'crontab';

    protected static $description = 'parse or convert crontab expression';

    public static function aliases(): array
    {
        return ['cron'];
    }

    protected static function commandAliases(): array
    {
        return [
            'execTime' => ['exec-time', 'execat', 'runat']
        ];
    }

    public function parseConfigure(Input $input): void
    {
        $input->bindArgument('statement', 0);
    }

    /**
     * parse the human readable statement to an cron expression.
     *
     * @arguments
     *  Statement       The human readable statement.
     *
     * @options
     *  -i          Run an interactive environment
     *
     * @example
     *  {binWithCmd} 'every 6 mins'         // '*\/6 * * * *'
     *  {binWithCmd} 'every day 10 am'      // '0 10 * * *'
     *  {binWithCmd} 'every day 10:20 am'   // '20 10 * * *'
     *
     * @param Input  $input
     * @param Output $output
     *
     * @throws Exception
     */
    public function parseCommand(Input $input, Output $output): void
    {
        $stat = (string)$input->getRequiredArg('statement');
        $output->colored("Input Statement: '$stat'");

        $expr = HuCron::fromStatement($stat);
        $output->writeln("Cron Expression: <suc>$expr</suc>");

        $dateList = $this->getNextNTimesRunDate($nextNum = 3, $expr);
        $output->aList($dateList, "Execution time for the next $nextNum times:");
    }

    /**
     * @param Input $input
     */
    public function execTimeConfigure(Input $input): void
    {
        $input->bindArgument('expression', 0);
    }

    /**
     * show next execution datetime for an cron expression.
     *
     * @arguments
     *  Expression      The cronTab expression. eg: `20 10 * * *`
     *
     * @options
     *  -n, --next NUMBER   Show next number exec datetime. default number is 3.
     *  -p, --prev          Show previsions exec datetime
     *
     * @param Input  $input
     * @param Output $output
     *
     * @throws Exception
     */
    public function execTimeCommand(Input $input, Output $output): void
    {
        $expr = (string)$input->getRequiredArg('expression');
        $output->colored('Cron Expression: ' . $expr);

        $nextNum  = $input->getSameIntOpt('n,next', 3);
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
