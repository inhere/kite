<?php declare(strict_types=1);

namespace Inhere\Kite\Console\Manager;

use Inhere\Kite\Common\CmdRunner;
use Inhere\Kite\Console\Model\BinTool;
use InvalidArgumentException;
use Toolkit\Cli\Cli;
use Toolkit\Stdlib\Obj;
use Toolkit\Stdlib\Str;
use function array_keys;
use function count;
use function in_array;

/**
 * class ToolsManager
 *
 * @author inhere
 * @date 2022/5/10
 */
class ToolManager
{
    private string $workdir = '';

    /**
     * @var array<string, array{desc: string, workdir: string, homepage: string, commands: array}>
     */
    private array $tools = [];

    /**
     * Class constructor.
     *
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        Obj::init($this, $config);
    }

    /**
     * @param string $name
     * @param string $command
     *
     * @return void
     */
    public function run(string $name, string $command): void
    {
        $this->runByModel($this->getToolModel($name), $command);
    }

    /**
     * @param BinTool $tool
     * @param string $command
     *
     * @return void
     */
    public function runByModel(BinTool $tool, string $command): void
    {
        $cr = $tool->buildCmdRunner($command);

        $cr->runAndPrint();
    }

    /**
     * @param string $command
     * @param CmdRunner $runner
     * @param BinTool $tool
     *
     * @return void
     */
    public function dispatch(string $command, CmdRunner $runner, BinTool $tool): void
    {
        if ($tips = $tool->getBeforeTip($command)) {
            Cli::writeln($tips);
        }

        $runner->runAndPrint();

        if ($tips = $tool->getAfterTip($command)) {
            Cli::writeln($tips);
        }
    }

    /**
     * @param string $toolName
     *
     * @return BinTool
     */
    public function getToolModel(string $toolName): BinTool
    {
        $tool  = $this->getTool($toolName);
        $model = BinTool::new($tool);

        if (!$model->workdir) {
            $model->workdir = $this->workdir;
        }

        $model->name = $toolName;
        return $model;
    }

    /**
     * @param string $keyword multi use comma split.
     * @param bool $and must match all keywords
     *
     * @return array<string, string>
     */
    public function searchTool(string $keyword, bool $and = true): array
    {
        $results  = [];
        $keywords = Str::explode($keyword, ' ');

        foreach ($this->tools as $name => $tool) {
            $toolDesc = $tool['desc'] ?? '';
            $nameDesc = "$name, $toolDesc";

            // full match
            if ($and) {
                if (Str::hasAll($nameDesc, $keywords)) {
                    $results[$name] = $toolDesc ?: "tool $name";
                }
            } elseif (Str::has($nameDesc, $keywords)) {
                $results[$name] = $toolDesc ?: "tool $name";
            }
        }

        return $results;
    }

    /**
     * @param array $names
     *
     * @return array<string, string>
     */
    public function getToolsInfo(array $names = []): array
    {
        $results = [];
        foreach ($this->tools as $name => $tool) {
            $toolDesc = $tool['desc'] ?? '';
            $homepage = $tool['homepage'] ?? '';

            // full match
            if ($names) {
                if (in_array($name, $names, true)) {
                    $results[$name] = $toolDesc ?: "tool $name";
                }
            } else {
                $results[$name] = $toolDesc ?: "tool $name";
            }

            if ($homepage) {
                $results[$name] .= "\nhomepage: $homepage";
            }
        }

        return $results;
    }

    /**
     * @return bool
     */
    public function isEmpty(): bool
    {
        return count($this->tools) === 0;
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function hasTool(string $name): bool
    {
        return isset($this->tools[$name]);
    }

    /**
     * @param string $toolName
     *
     * @return array
     */
    public function getTool(string $toolName): array
    {
        if (!$this->hasTool($toolName)) {
            throw new InvalidArgumentException("tool name '$toolName' is not registered");
        }

        return $this->tools[$toolName];
    }

    /**
     * @param string $toolName
     * @param string $command
     *
     * @return string|array
     */
    public function getCommand(string $toolName, string $command): string|array
    {
        $tool = $this->getTool($toolName);
        if (!isset($tool[$command])) {
            throw new InvalidArgumentException("not found '$command' command in the tool '$toolName'");
        }

        return $tool[$command];
    }

    /**
     * @return array
     */
    public function getTools(): array
    {
        return $this->tools;
    }

    /**
     * @return array
     */
    public function getNames(): array
    {
        return array_keys($this->tools);
    }

    /**
     * @param array $tools
     */
    public function setTools(array $tools): void
    {
        $this->tools = $tools;
    }

    /**
     * @return string
     */
    public function getWorkdir(): string
    {
        return $this->workdir;
    }

    /**
     * @param string $workdir
     */
    public function setWorkdir(string $workdir): void
    {
        $this->workdir = $workdir;
    }
}
