<?php declare(strict_types=1);
/**
 * This file is part of Kite.
 *
 * @link     https://github.com/inhere
 * @author   https://github.com/inhere
 * @license  MIT
 */

namespace Inhere\Kite\Console\Controller\Gitx;

use Inhere\Console\Controller;
use Inhere\Console\IO\Input;
use Inhere\Console\IO\Output;
use InvalidArgumentException;
use RuntimeException;
use Toolkit\PFlag\FlagsParser;
use Toolkit\Sys\Sys;
use function array_filter;
use function array_intersect;
use function count;
use function implode;
use function is_dir;
use function is_file;
use function sprintf;
use function str_pad;
use const PHP_EOL;

/**
 * Internal tool for toolkit development
 *
 * @author inhere
 */
class GitSubtreeController extends Controller
{
    public const TYPE_SSL   = 'git@github.com:';
    public const TYPE_HTTPS = 'https://github.com/';

    protected static string $name = 'git-sub';
    protected static string $desc = 'quick the git subtree tool';

    /**
     * @var string
     * https eg. https://github.com/php-toolkit/php-utils.git
     * ssl eg. git@github.com:php-toolkit/php-utils.git
     */
    public string $gitUrl = '%sphp-toolkit/%s.git';

    /** @var array */
    public array $components = [];

    /** @var string */
    public string $componentDir;

    public static function aliases(): array
    {
        return ['git-subtree', 'gitsub', 'subtree'];
    }

    /**
     * List all swoft component names in the php-toolkit/toolkit
     *
     * @options
     *  --show-repo        bool;Display remote git repository address.
     *
     * @param FlagsParser $fs
     * @param Output $output
     *
     * @return int
     * @example
     *  {fullCommand}
     *  {fullCommand} --show-repo
     */
    public function listCommand(FlagsParser $fs,  Output $output): int
    {
        $this->checkEnv();

        $output->colored('Components Total: ' . count($this->components));

        $buffer   = [];
        $showRepo = $fs->getOpt('show-repo');

        foreach ($this->components as $component) {
            if (!$showRepo) {
                $buffer[] = " $component";
                continue;
            }

            $remote    = sprintf($this->gitUrl, self::TYPE_HTTPS, $component);
            $component = str_pad($component, 20);
            $buffer[]  = sprintf('  <comment>%s</comment> -  %s', $component, $remote);
        }

        $output->writeln($buffer);

        return 0;
    }

    /**
     * Add component directory code from git repo by 'git subtree add'
     *
     * @usage {fullCommand} [COMPONENTS ...] [--OPTION ...]
     *
     * @arguments
     *  Component[s]   The existing component name[s], allow multi by space.
     *
     * @options
     *  --squash        bool;Add option '--squash' in git subtree add command. default: <info>True</info>
     *  --dry-run       bool;Just print all the commands, but do not execute them. default: <info>False</info>
     *  -a, --all       bool;Pull all components from them git repo. default: <info>False</info>
     *  -y, --yes       bool;Do not confirm when execute git subtree push. default: <info>False</info>
     *  --show-result   bool;Display result for git subtree command exec. default: <info>False</info>
     *
     * @param Input $input
     * @param FlagsParser $fs
     * @param Output $output
     *
     * @return int
     * @example
     *  {fullCommand} collection         Pull the collection from it's git repo
     *  {fullCommand} collection di      Pull multi component
     */
    public function addCommand(Input $input, FlagsParser $fs, Output $output): int
    {
        $config = [
            'operate'       => 'add',
            'operatedNames' => 'Will added components',
            'begin'         => 'Execute the add command',
            'doing'         => 'Adding',
            'done'          => "\nOK, A total of 【%s】 components were successfully added"
        ];

        $config['onExec'] = function (string $name) use ($output) {
            $libPath = $this->componentDir . '/libs/' . $name;

            if (is_dir($libPath)) {
                $output->liteWarning("Component cannot be repeat add: $name");

                return false;
            }

            return true;
        };

        return $this->runGitSubtree($input, $fs, $output, $config);
    }

    /**
     * Update component directory code from git repo by 'git subtree pull'
     *
     * @usage {fullCommand} [COMPONENTS ...] [--OPTION ...]
     * @arguments
     *  Component[s]   The existing component name[s], allow multi by space.
     * @options
     *  --squash        bool;Add option '--squash' in git subtree pull command. default: <info>True</info>
     *  --dry-run       bool;Just print all the commands, but do not execute them. default: <info>False</info>
     *  -a, --all       bool;Pull all components from them's git repo. default: <info>False</info>
     *  -y, --yes       bool;Do not confirm when execute git subtree push. default: <info>False</info>
     *  --show-result   bool;Display result for git subtree command exec. default: <info>False</info>
     *
     * @param Input $input
     * @param FlagsParser $fs
     * @param Output $output
     *
     * @return int
     * @example
     *  {fullCommand} collection              Pull the collection from it's git repo
     *  {fullCommand} collection console      Pull multi component
     */
    public function pullCommand(Input $input, FlagsParser $fs, Output $output): int
    {
        $config = [
            'operate'       => 'pull',
            'operatedNames' => 'Will pulled components',
            'begin'         => 'Execute the pull command',
            'doing'         => 'Pulling',
            'done'          => "\nOK, A total of 【%s】 components were successfully pulled"
        ];

        return $this->runGitSubtree($input, $fs,$output, $config);
    }

    /**
     * Push component[s] directory code to component's repo by 'git subtree push'
     *
     * @usage {fullCommand} [COMPONENTS ...] [--OPTION ...]
     * @arguments
     *  Component[s]   The existing component name[s], allow multi by space.
     * @options
     *  --type          Remote git repository address usage protocol. allow: https, ssl. default: <info>https</info>
     *  -a, --all       bool;Push all components to them's git repo. default: <info>False</info>
     *  -y, --yes       bool;Do not confirm when execute git subtree push. default: <info>False</info>
     *  --dry-run       bool;Just print all the commands, but do not execute them. default: <info>False</info>
     *  --squash        bool;Add option '--squash' in git subtree push command. default: <info>True</info>
     *  --show-result   bool;Display result for git subtree command exec. default: <info>False</info>
     *
     * @param Input $input
     * @param FlagsParser $fs
     * @param Output $output
     *
     * @return int
     * @example
     *  {fullCommand} collection              Push the collection to it's git repo
     *  {fullCommand} collection console      Push multi component. collection and console
     *  {fullCommand} --all                Push all components
     *  {fullCommand} --all --dry-run      Push all components, but do not execute.
     */
    public function pushCommand(Input $input, FlagsParser $fs, Output $output): int
    {
        $config = [
            'operate'       => 'push',
            'operatedNames' => 'Will pushed components',
            'begin'         => 'Execute the push command',
            'doing'         => 'Pushing',
            'done'          => "\nOK, A total of 【%s】 components are pushed to their respective git repositories",
        ];

        return $this->runGitSubtree($input, $fs, $output, $config);
    }

    /**
     * @param Input $input
     * @param FlagsParser $fs
     * @param Output $output
     * @param array $config
     *
     * @return int
     */
    protected function runGitSubtree(Input $input, FlagsParser $fs, Output $output, array $config): int
    {
        $this->checkEnv();
        $output->writeln("<comment>Component Directory</comment>:\n $this->componentDir");

        $operate = $config['operate'];
        // $names   = array_filter($input->getArgs(), 'is_int', ARRAY_FILTER_USE_KEY);
        $names = array_filter($fs->getRawArgs(), 'is_int', ARRAY_FILTER_USE_KEY);

        if ($names) {
            $back  = $names;
            $names = array_intersect($names, $this->components);

            if (!$names) {
                throw new RuntimeException('Invalid component name entered: ' . implode(', ', $back));
            }
        } elseif ($fs->getOpt('all', false)) {
            $names = $this->components;
        } else {
            throw new RuntimeException('Please enter the name of the component that needs to be operated');
        }

        $output->writeln([
            "<comment>{$config['operatedNames']}</comment>:",
            ' <info>' . implode(', ', $names) . '</info>'
        ]);

        $doneOne = ' OK';
        $tryRun  = $fs->getOpt('dry-run', false);
        $squash  = $fs->getOpt('squash', true) ? ' --squash' : '';

        $protoType = $fs->getOpt('type') ?: 'https';
        $protoHost = $protoType === 'ssl' ? self::TYPE_SSL : self::TYPE_HTTPS;
        $workDir   = $this->componentDir;
        $onExec    = $config['onExec'] ?? null;

        // eg. git subtree push --prefix=src/view git@github.com:php-toolkit/php-utils.git master [--squash]
        $output->writeln("\n<comment>{$config['begin']}</comment>:");

        foreach ($names as $name) {
            if ($onExec && !$onExec($name)) {
                continue;
            }

            $ret     = null;
            $remote  = sprintf($this->gitUrl, $protoHost, $name);
            $command = sprintf('git subtree %s --prefix=libs/%s %s master%s', $operate, $name, $remote, $squash);

            $output->writeln("> <cyan>$command</cyan>");
            $output->write("{$config['doing']} '$name' ...", false);

            // if '--dry-run' is true. do not exec.
            if (!$tryRun) {
                [$code, $ret, $err] = Sys::run($command, $workDir);

                if ($code !== 0) {
                    throw new RuntimeException("Exec command failed. command: $command error: $err \nreturn: \n$ret");
                }
            }

            $output->colored($doneOne, 'success');

            if ($ret && $fs->getOpt('show-result')) {
                $output->writeln(PHP_EOL . $ret);
            }
        }

        $output->colored(sprintf($config['done'], count($names)), 'success');

        return 0;
    }

    /**
     * Generate classes API documents by 'sami/sami'
     *
     * @options
     *  --sami          The sami.phar package absolute path.
     *  --force         bool;The option forces a rebuild docs. default: <info>False</info>
     *  --dry-run       bool;Just print all the commands, but do not execute them. default: <info>False</info>
     *  --show-result   bool;Display result for the docs generate. default: <info>False</info>
     *
     * @param FlagsParser $fs
     * @param Output $output
     *
     * @return int
     * @example
     *  {fullCommand} --sami ~/Workspace/php/tools/sami.phar --force --show-result
     *
     *  About sami:
     *   - An API documentation generator
     *   - github https://github.com/FriendsOfPHP/Sami
     *   - download `curl -O http://get.sensiolabs.org/sami.phar`
     */
    public function genApiCommand(FlagsParser $fs, Output $output): int
    {
        $this->checkEnv();

        $option = '';

        if (!$samiPath = $fs->getOpt('sami')) {
            $output->colored("Please input the sami.phar path by option '--sami'", 'error');

            return -1;
        }

        if (!is_file($samiPath)) {
            $output->colored('The sami.phar file is not exists! File: ' . $samiPath, 'error');

            return -1;
        }

        $tryRun  = (bool)$fs->getOpt('dry-run', false);
        $config  = $this->componentDir . '/sami.doc.inc';
        $workDir = $this->componentDir;

        if ($fs->getOpt('force')) {
            $option .= ' --force';
        }

        // php ~/Workspace/php/tools/sami.phar render --force
        $command = sprintf(
            'php ~/Workspace/php/tools/sami.phar %s %s%s',
            'update',
            $config,
            $option
        );

        $output->writeln("> <cyan>$command</cyan>");

        // if '--dry-run' is true. do not exec.
        if (!$tryRun) {
            [$code, $ret,] = Sys::run($command, $workDir);

            if ($code !== 0) {
                throw new RuntimeException("Exec command failed. command: $command return: \n$ret");
            }

            if ($fs->getOpt('show-result')) {
                $output->writeln(PHP_EOL . $ret);
            }
        }

        $output->colored("\nOK, Classes reference documents generated!");

        return 0;
    }

    private function checkEnv(): void
    {
        $this->componentDir = $this->input->getPwd();

        $file = $this->componentDir . '/sub-libs.php';

        if (!is_file($file)) {
            throw new InvalidArgumentException("Missing the components config, file: $file");
        }

        $this->components = require $file;
    }
}

