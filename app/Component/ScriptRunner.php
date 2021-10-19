<?php declare(strict_types=1);

namespace Inhere\Kite\Component;

use Inhere\Console\Util\Show;
use Inhere\Kite\Helper\SysCmd;
use InvalidArgumentException;
use RuntimeException;
use Toolkit\Cli\Cli;
use Toolkit\FsUtil\Dir;
use Toolkit\FsUtil\File;
use Toolkit\Stdlib\Obj\AbstractObj;
use function array_filter;
use function array_map;
use function array_merge;
use function basename;
use function count;
use function explode;
use function implode;
use function in_array;
use function is_array;
use function is_dir;
use function is_file;
use function is_scalar;
use function is_string;
use function json_encode;
use function preg_match;
use function stripos;
use function strpos;
use function substr;
use function trim;
use function vdump;

/**
 * class ScriptRunner
 */
class ScriptRunner extends AbstractObj
{
    public const TYPE_CMD  = 'cmd';
    public const TYPE_FILE = 'file';

    /**
     * @var bool
     */
    private $dryRun = false;

    /**
     * @var bool
     */
    private $enable = true;

    /**
     * @var array
     */
    private $envs = [];

    /**
     * @var array
     */
    private $vars = [];

    /**
     * @var array
     */
    private $scripts = [];

    /**
     * @var array
     */
    private $scriptDirs = [];

    /**
     * @var array
     */
    private $scriptExts = ['.sh', '.zsh', '.bash', '.php', '.go', '.gop'];

    /**
     * @var array
     */
    private $scriptFiles = [];

    /**
     * Whether allow auto match bin by ext name.
     *
     * @var bool
     */
    private $autoScriptBin = true;

    /**
     * If not set the shebang line, will find bin by ext
     *
     * @var string[]
     */
    private $scriptExt2bin = [
        '.sh'   => 'sh',
        '.zsh'  => 'zsh',
        '.bash' => 'bash',
        '.php'  => 'php',
        '.go'   => 'go run',
    ];

    /**
     * @param string $name
     * @param array  $runArgs
     */
    public function run(string $name, array $runArgs): void
    {
        if ($this->isScriptName($name)) {
            $this->runCustomScript($name, $runArgs);
        } elseif ($this->isScriptFile($name)) {
            $this->runScriptFile($name, $runArgs);
        } else {
            throw new InvalidArgumentException("invalid script command or script file. (name: $name)");
        }
    }

    /**
     * run script by name
     *
     * @param string $name
     * @param array  $runArgs
     */
    public function runCustomScript(string $name, array $runArgs): void
    {
        if (!isset($this->scripts[$name])) {
            throw new InvalidArgumentException("The script name:$name not exists");
        }

        // script commands
        $commands = $this->scripts[$name];

        // run script commands
        $this->executeScripts($name, $runArgs, $commands);
    }

    /**
     * @param string $name
     * @param array  $runArgs
     * @param mixed  $commands
     */
    private function executeScripts(string $name, array $runArgs, $commands): void
    {
        if (is_string($commands)) {
            // bash -c "echo hello"
            // bash some.sh
            if ($scriptFile = $this->findScriptFile($commands)) {
                $this->runScriptFile($scriptFile, $runArgs);
                return;
            }

            $command = $this->replaceScriptVars($name, $commands, $runArgs);
            // CmdRunner::new($command)->do(true);
            $this->executeScript($command, true);
            return;
        }

        if (is_array($commands)) {
            if (isset($commands['_meta'])) {
                unset($commands['_meta']);
            }

            foreach ($commands as $index => $command) {
                $pos = $name . '.' . $index;
                if (!$command) {
                    Show::liteError("The script $pos command is empty, skip run it");
                    continue;
                }

                if (!is_string($command)) {
                    Show::liteError("The script $pos command is not string, skip run it");
                    continue;
                }

                // bash -c "echo hello"
                // bash some.sh
                if ($scriptFile = $this->findScriptFile($command)) {
                    $this->runScriptFile($scriptFile, $runArgs);
                    continue;
                }

                $command = $this->replaceScriptVars($name, $command, $runArgs);
                $this->executeScript($command);
            }
            return;
        }

        Show::error("invalid script commands for '$name', only allow: string, string[]");
    }

    /**
     * @param string $name
     * @param string $cmdString
     * @param array  $scriptArgs
     *
     * @return string
     */
    private function replaceScriptVars(string $name, string $cmdString, array $scriptArgs): string
    {
        if (strpos($cmdString, '$') === false) {
            return $cmdString;
        }

        if (!$scriptArgs) {
            // has vars $@ $1 ... $3 ...
            $matches = [];
            preg_match('/\$[\d+|@]/', $cmdString, $matches);
            if ($matches) {
                throw new InvalidArgumentException("missing arguments for run script '$name', detail: '$cmdString'");
            }
        }

        $full  = implode(' ', $scriptArgs);
        $pairs = [
            '$@' => $full,
            '$?' => $full, // optional all vars
        ];

        // like bash script, first var is '$1'
        foreach ($scriptArgs as $i => $arg) {
            $k = '$' . ($i + 1);
            // add
            $pairs[$k] = $arg;
        }

        return strtr($cmdString, $pairs);
    }

    /**
     * @param string $scriptFile
     * @param array  $runArgs
     */
    public function runScriptFile(string $scriptFile, array $runArgs): void
    {
        if (!is_file($scriptFile)) {
            throw new InvalidArgumentException("The script file:$scriptFile not exists");
        }

        // #!/usr/bin/env bash
        // #!/usr/bin/bash
        $line = File::readFirstLine($scriptFile);
        $name = basename($scriptFile);

        // must start withs '#!'
        if (!$line || strpos($line, '#!') !== 0) {
            Cli::colored("will direct run the script file: $name", 'cyan');

            $command = $scriptFile;
            $extName = File::getExtension($name);

            // auto use bin by file ext.
            if ($this->autoScriptBin && isset($this->scriptExt2bin[$extName])) {
                $binName = $this->scriptExt2bin[$extName];
                $command = "$binName $scriptFile";
            }
        } else {
            Cli::colored("will run the script file: $name (shebang: $line)", 'cyan');

            // eg:
            // '#!/usr/bin/env bash'
            // '#!/usr/bin/env -S gop run'
            if (strpos($line, ' ') > 0) {
                [, $binName] = explode(' ', $line, 2);
                if (strpos($binName, '-S ') === 0) {
                    $binName = substr($binName, 3);
                }
            } else { // eg: '#!/usr/bin/bash'
                $binName = trim($line, '#!');
            }

            // eg: "bash hello.sh"
            $command = "$binName $scriptFile";
        }

        if ($runArgs) {
            $command .= ' ' . implode(' ', $runArgs);
        }

        $this->executeScript($command);
    }

    /**
     * @param string $command
     * @param bool   $onlyOne
     */
    private function executeScript(string $command, bool $onlyOne = false): void
    {
        // CmdRunner::new($command)->do(true);
        if ($this->dryRun) {
            Cli::colored('DRY-RUN: ' . $command, 'cyan');
        } else {
            SysCmd::quickExec($command);
        }

        if ($onlyOne) {
            Cli::println('');
        }

        Cli::colored("DONE:\n $command");
    }

    /**
     * @param string $name
     *
     * @return string
     */
    public function findScriptFile(string $name): string
    {
        $ext = File::getExtension($name);
        if (!$ext || !in_array($ext, $this->scriptExts, true)) {
            return '';
        }

        if (is_file($name)) {
            return $name;
        }

        foreach ($this->scriptDirs as $scriptDir) {
            $relativeFile = $scriptDir . '/' . $name;
            if (is_file($relativeFile)) {
                return $relativeFile;
            }
        }

        return '';
    }

    public function loadAllScriptFiles(): void
    {
        // $extMatch = '';

        foreach ($this->scriptDirs as $scriptDir) {
            // $iter = Dir::getIterator($scriptDir);
            // $files = Dir::getFiles($scriptDir, $extMatch);
        }
    }

    /**
     * @param string $keyword
     *
     * @return array
     */
    public function getAllScriptFiles(string $keyword = ''): array
    {
        $extMatch = implode('|', array_map(static function ($ext) {
            return trim($ext, '.');
        }, $this->scriptExts));

        $files = [];
        foreach ($this->scriptDirs as $dir) {
            $dir = Dir::realpath($dir);

            if (!is_dir($dir)) {
                throw new RuntimeException("script dir '$dir' - is not exists");
            }

            // $iter = Dir::getIterator($scriptDir);
            $files = Dir::getFiles($dir, $extMatch, true, $dir . '/', $files);
        }

        if ($keyword) {
            $files = array_filter($files, static function ($file) use($keyword) {
                return stripos($file, $keyword) !== false;
            });
        }

        return $files;
    }

    /**
     * @param string $kw
     *
     * @return array
     */
    public function searchScripts(string $kw): array
    {
        $matched = [];
        foreach ($this->scripts as $name => $item) {
            if (strpos($name, $kw) !== false) {
                $matched[$name] = $item;
            } else {
                $itemString = is_scalar($item) ? (string)$item : json_encode($item);

                if (strpos($itemString, $kw) !== false) {
                    $matched[$name] = $item;
                }
            }
        }

        return $matched;
    }

    // -------------------------------- scripts --------------------------------

    /**
     * @param string $name
     *
     * @return mixed|null
     */
    public function getScript(string $name)
    {
        return $this->scripts[$name] ?? null;
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function isScriptName(string $name): bool
    {
        return isset($this->scripts[$name]);
    }

    /**
     * @return array
     */
    public function getScripts(): array
    {
        return $this->scripts;
    }

    /**
     * @param array $scripts
     */
    public function setScripts(array $scripts): void
    {
        $this->scripts = $scripts;
    }

    /**
     * @return int
     */
    public function getScriptCount(): int
    {
        return count($this->scripts);
    }

    // -------------------------------- scriptFiles --------------------------------

    /**
     * @param string $name
     *
     * @return bool
     */
    public function isScriptFile(string $name): bool
    {
        return $this->findScriptFile($name) !== '';
    }

    /**
     * @param string $ext
     *
     * @return bool
     */
    public function isAllowedExt(string $ext): bool
    {
        return in_array($ext, $this->scriptExts, true);
    }

    /**
     * @return array
     */
    public function getScriptDirs(): array
    {
        return $this->scriptDirs;
    }

    /**
     * @param array $scriptDirs
     */
    public function setScriptDirs(array $scriptDirs): void
    {
        $this->scriptDirs = $scriptDirs;
    }

    /**
     * @return array
     */
    public function getScriptFiles(): array
    {
        return $this->scriptFiles;
    }

    /**
     * @return bool
     */
    public function isAutoScriptBin(): bool
    {
        return $this->autoScriptBin;
    }

    /**
     * @param bool|string $autoScriptBin
     */
    public function setAutoScriptBin($autoScriptBin): void
    {
        $this->autoScriptBin = (bool)$autoScriptBin;
    }

    /**
     * @return string[]
     */
    public function getScriptExt2bin(): array
    {
        return $this->scriptExt2bin;
    }

    /**
     * @param array<string> $scriptExt2bin
     */
    public function setScriptExt2bin(array $scriptExt2bin): void
    {
        $this->scriptExt2bin = array_merge($this->scriptExt2bin, $scriptExt2bin);
    }

    /**
     * @return array
     */
    public function getScriptExts(): array
    {
        return $this->scriptExts;
    }

    /**
     * @param array $scriptExts
     */
    public function setScriptExts(array $scriptExts): void
    {
        $this->scriptExts = $scriptExts;
    }

    // -------------------------------- others --------------------------------

    /**
     * @return bool
     */
    public function isDryRun(): bool
    {
        return $this->dryRun;
    }

    /**
     * @param bool $dryRun
     */
    public function setDryRun(bool $dryRun): void
    {
        $this->dryRun = $dryRun;
    }

    /**
     * @return bool
     */
    public function isEnable(): bool
    {
        return $this->enable;
    }

    /**
     * @param bool|int $enable
     */
    public function setEnable($enable): void
    {
        $this->enable = (bool)$enable;
    }

}
