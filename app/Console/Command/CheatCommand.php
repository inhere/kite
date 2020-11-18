<?php declare(strict_types=1);
/**
 * This file is part of Kite.
 *
 * @link     https://github.com/inhere
 * @author   https://github.com/inhere
 * @license  MIT
 */

namespace Inhere\Kite\Console\Command;

use Inhere\Console\Command;
use Inhere\Console\IO\Input;
use Inhere\Console\IO\Output;

/**
 * Class CheatCommand
 */
class CheatCommand extends Command
{
    protected static $name = 'cheat';

    protected static $description = 'Query cheat for development';

    public static function aliases(): array
    {
        return ['cht', 'cht.sh', 'cheat.sh'];
    }

    /**
     * Configure command
     */
    protected function configure(): void
    {
        $def = $this->createDefinition();

        $def->addArgument('lang', Input::ARG_REQUIRED, 'The language for search. eg: go, php, java, lua, python, js ...');

        $def->addOption('Q', 'Q', Input::OPT_BOOLEAN, 'query');
        $def->addOption('t', 'T', Input::OPT_BOOLEAN, 'query');

        $def->setExample([
            '{fullCmd} go reverse list'
        ]);
    }

    /**
     * Query cheat for development
     * github: https://github.com/chubin/cheat.sh
     *
     * curl cheat.sh/tar
     * curl cht.sh/curl
     * curl https://cheat.sh/rsync
     * curl https://cht.sh/php
     *
     * curl cht.sh/go/:list
     * curl cht.sh/go/reverse+a+list
     * curl cht.sh/python/random+list+elements
     * curl cht.sh/js/parse+json
     * curl cht.sh/lua/merge+tables
     * curl cht.sh/clojure/variadic+function
     *
     * @param Input  $input
     * @param Output $output
     */
    protected function execute($input, $output)
    {
        $output->write('hello, this in ' . __METHOD__);

        $host = 'https://cht.sh';
        $lang = $input->getStringArg('lang');

        $chtUrl = \sprintf('%s/%s', $host, $lang);

        $output->info('will request: ' . $chtUrl);
        $result = \file_get_contents($chtUrl);

        $output->colored('RESULT:');
        $output->writeln($result);
    }
}
