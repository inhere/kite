<?php declare(strict_types=1);

namespace Inhere\Kite\Console\SubCmd\ToolCmd;

use Inhere\Console\Command;
use Inhere\Console\IO\Input;
use Inhere\Console\IO\Output;
use Inhere\Kite\Console\Component\ContentsAutoReader;
use Inhere\Kite\Helper\AppHelper;
use function hash;
use function md5;
use function strtolower;
use function strtoupper;

/**
 * class RandomCommand
 *
 * @author inhere
 */
class RandomCommand extends Command
{
    protected static string $name = 'random';
    protected static string $desc = 'generate an random string';

    public static function aliases(): array
    {
        return ['rdm', 'rand'];
    }

    protected function configure(): void
    {
        // $this->flags->addOptByRule($name, $rule);
        // $this->flags->addArg('str', 'want to signed string. allow: @c', 'string', true);
        // $this->flags->addOpt('algo', 'a', 'Name of selected hashing algorithm. eg: md5, sha256', 'string', false, 'md5');
        // $this->flags->addOpt(
        //     'key', 'k',
        //     'Shared secret key used for generating the HMAC variant of the message digest.',
        //     'string', true);
    }

    /**
     *
     * @options
     *  -l, --length        int;The generate string length;;15
     *  -n, --number        int;The number of generated strings. default: 1
     *  -t, --template      The sample template name.
     *                       allow: alpha/a, alpha_num/an, alpha_num_up/anu, alpha_num_c/anc
     *
     * @param Input $input
     * @param Output $output
     * @return void
     */
    protected function execute(Input $input, Output $output)
    {
        $fs = $this->flags;

        $length = $fs->getOpt('length', 15);
        $number = $fs->getOpt('number', 1);

        if ($number < 1 || $number > 20) {
            $number = 1;
        }

        $sname = $fs->getOpt('template', 'alpha_num');

        if ($number === 1) {
            $str = AppHelper::genRandomStr($sname, $length);
            $output->info('Generated: ' . $str);
            return;
        }

        $list = ['Generated:'];
        for ($i = 0; $i < $number; $i++) {
            $list[] = AppHelper::genRandomStr($sname, $length);
        }

        $output->info($list);
    }
}
