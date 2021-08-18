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
use Inhere\Kite\Kite;
use Inhere\Kite\Model\Logic\KiteInitLogic;
use Toolkit\FsUtil\FS;

/**
 * Class InitCommand
 */
class InitCommand extends Command
{
    protected static $name = 'init';

    protected static $description = 'initialize kite on the system';

    /**
     * @options
     *  -y, --yes           Not confirm anything
     *      --dry-run       Dry run, not real execute any commands.
     *
     * @param Input  $input
     * @param Output $output
     */
    protected function execute($input, $output)
    {
        $yes = $input->getSameBoolOpt('y,yes', false);

        $logic = new KiteInitLogic([
            'workDir' => $input->getWorkDir(),
            'kiteDir' => Kite::basePath(),
        ]);

        $dryRun = $input->getBoolOpt('dry-run');
        $logic->setDryRun($dryRun);

        $output->info('init kite runtime config');
        $logic->initConfig();

        $output->info('generate auto completion script file');
        if (!$yes && $output->confirm('generate?', false)) {
            $genFile = FS::realpath('~/.oh-my-zsh/custom/plugins/kite/kite.plugin.zsh');
            $tplFile = Kite::getPath('resource/templates/completion/zsh.plugin.tpl');
            $logic->installCompleter($genFile, '', $tplFile);
        }

        // $output->colored('Completed');
        $result = $logic->getResult();
        $output->writeln($result);
    }
}
