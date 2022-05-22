<?php declare(strict_types=1);

namespace Inhere\Kite\Console\Model;

use Inhere\Kite\Common\CmdRunner;
use InvalidArgumentException;
use Toolkit\Cli\Cli;
use Toolkit\Stdlib\Obj;
use Toolkit\Stdlib\Obj\AbstractObj;
use function in_array;
use function is_string;
use function substr;

/**
 * class BinTool
 *
 * @author inhere
 * @date 2022/5/10
 */
class BinTool extends AbstractObj
{
    public const BUILT_IN = ['install', 'update', 'remove'];

    public string $name = '';
    public string $desc = '';

    public string $workdir = '';
    public string $homepage = '';

    public array $envVars = [];

    /**
     * @var string[]
     */
    protected array $deps = [];

    /**
     * @var array{run: array, deps: array}
     * @see ToolCmdMeta
     */
    protected array $install = [];

    /**
     * @var array{run: array, deps: array}
     * @see ToolCmdMeta
     */
    protected array $update = [];

    /**
     * @var array{run: array, deps: array}
     * @see ToolCmdMeta
     */
    protected array $remove = [];

    public array $commands = [];

    public array $beforeTips = [];
    public array $afterTips = [];

    /**
     * @param array $data
     *
     * @return $this
     */
    public function loadData(array $data): self
    {
        Obj::init($this, $data);
        return $this;
    }

    /**
     * @param string $command
     *
     * @return CmdRunner
     */
    public function buildCmdRunner(string $command): CmdRunner
    {
        $scripts = $this->getCmdScripts($command);

        return CmdRunner::new($scripts, $this->workdir);
    }

    /**
     * @param string $command
     * @param CmdRunner $runner
     *
     * @return void
     */
    public function run(string $command, CmdRunner $runner): void
    {
        if ($tips = $this->getBeforeTip($command)) {
            Cli::cyan('Beginning.tips:');
            Cli::writeln($tips);
        }

        $runner->runAndPrint();

        if ($tips = $this->getAfterTip($command)) {
            Cli::cyan('Complete.tips:');
            Cli::writeln($tips);
        }
    }

    /**
     * @param string $command
     *
     * @return string|array
     */
    public function getCmdScripts(string $command): string|array
    {
        $this->mustCommand($command);
        $name = $this->name;
        $cmd  = $this->commands[$command];

        if (is_string($cmd) && str_starts_with($cmd, '@')) {
            $refCmd = substr($cmd, 1);
            if (!$this->hasCommand($refCmd)) {
                throw new InvalidArgumentException("not found refer command '$refCmd' in the tool '$name'");
            }

            // use refer command
            $cmd = $this->commands[$refCmd];
        }

        return $cmd;
    }

    /**
     * @param string $command
     *
     * @return string|array
     */
    public function getCommand(string $command): string|array
    {
        $this->mustCommand($command);

        return $this->commands[$command];
    }

    /**
     * @param string $command
     *
     * @return bool
     */
    public function hasCommand(string $command): bool
    {
        return isset($this->commands[$command]);
    }

    /**
     * @param string $command
     *
     * @return void
     */
    private function mustCommand(string $command): void
    {
        if ($this->isBuiltIn($command)) {
            return;
        }

        if (!isset($this->commands[$command])) {
            throw new InvalidArgumentException("command '$command' is not found in tool: " . $this->name);
        }
    }

    /**
     * @param string $cmd
     *
     * @return bool
     */
    public function isBuiltIn(string $cmd): bool
    {
        return in_array($cmd, self::BUILT_IN, true);
    }

    /**
     * @param array $commands
     *
     * @return BinTool
     */
    public function setCommands(array $commands): self
    {
        $this->commands = $commands;
        return $this;
    }

    /**
     * @param string $command
     *
     * @return array|string
     */
    public function getBeforeTip(string $command): array|string
    {
        return $this->beforeTips[$command] ?? '';
    }

    /**
     * @param string $command
     *
     * @return array|string
     */
    public function getAfterTip(string $command): array|string
    {
        return $this->afterTips[$command] ?? '';
    }

    /**
     * @param array|string $deps
     */
    public function setDeps(array|string $deps): void
    {
        $this->deps = (array)$deps;
    }

    /**
     * @return array
     */
    public function getInstall(): array
    {
        return $this->install;
    }

    /**
     * @param array|string $install
     */
    public function setInstall(array|string $install): void
    {
        if (is_string($install)) {
            $install = [
                'run' => $install,
            ];
        }

        $this->install = $install;
    }

    /**
     * @return array
     */
    public function getUpdate(): array
    {
        return $this->update;
    }

    /**
     * @param array|string $update
     */
    public function setUpdate(array|string $update): void
    {
        if (is_string($update)) {
            $update = [
                'run' => $update,
            ];
        }

        $this->update = $update;
    }

    /**
     * @return array
     */
    public function getRemove(): array
    {
        return $this->remove;
    }

    /**
     * @param array|string $remove
     */
    public function setRemove(array|string $remove): void
    {
        if (is_string($remove)) {
            $remove = [
                'run' => $remove,
            ];
        }

        $this->remove = $remove;
    }

}
