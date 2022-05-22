<?php declare(strict_types=1);

namespace Inhere\Kite\Component;

use Inhere\Console\Util\Show;
use Inhere\Kite\Concern\SimpleEventAwareTrait;
use Inhere\Kite\Helper\KiteUtil;
use Inhere\Kite\Helper\SysCmd;
use Inhere\Kite\Kite;
use InvalidArgumentException;
use RuntimeException;
use Toolkit\Cli\Cli;
use Toolkit\FsUtil\Dir;
use Toolkit\FsUtil\File;
use Toolkit\Stdlib\Json;
use Toolkit\Stdlib\Obj\AbstractObj;
use Toolkit\Stdlib\OS;
use function array_filter;
use function array_map;
use function array_merge;
use function basename;
use function count;
use function dirname;
use function explode;
use function implode;
use function in_array;
use function is_array;
use function is_dir;
use function is_file;
use function is_scalar;
use function is_string;
use function ltrim;
use function preg_match;
use function stripos;
use function strpos;
use function substr;
use function trim;

/**
 * class ScriptRunner
 */
class ScriptRunner extends AbstractObj
{
    use SimpleEventAwareTrait;

    public const TYPE_CMD  = 'cmd';
    public const TYPE_FILE = 'file';

    /**
     * handler: function(string $binName, array $args) {}
     */
    public const EVT_ON_RUN_BEFORE = 'script.run.before';
    public const EVT_ON_RUN_AFTER  = 'script.run.after';

    /**
     * @var bool
     */
    private bool $dryRun = false;

    /**
     * @var bool
     */
    private bool $enable = true;

    /**
     * @var int exit code of exec script
     */
    private int $errCode = 0;

    /**
     * @var array
     */
    private array $envs = [];

    /**
     * @var array
     */
    private array $vars = [];

    /**
     * @var array
     */
    public array $scripts = [];

    /**
     * @var array
     */
    public array $scriptDirs = [];

    /**
     * Allowed script file ext list.
     *
     * @var array
     */
    private array $scriptExts = ['.sh', '.zsh', '.bash', '.php', '.go', '.gop', '.kts', '.gry', '.java', '.groovy'];

    /**
     * @var array
     */
    private array $scriptFiles = [];

    /**
     * Whether allow auto match bin by ext name.
     *
     * @var bool
     */
    private bool $autoScriptBin = true;

    /**
     * If not set the shebang line, will find bin by ext
     *
     * @var string[]
     */
    private array $scriptExt2bin = [
        '.sh'     => 'sh',
        '.zsh'    => 'zsh',
        '.bash'   => 'bash',
        '.php'    => 'php',
        '.gry'    => 'groovy',
        '.groovy' => 'groovy',
        '.go'     => 'go run',
    ];

    /**
     * Class constructor.
     *
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        parent::__construct($config);

        $this->setSupportEvents([self::EVT_ON_RUN_BEFORE, self::EVT_ON_RUN_AFTER]);
    }

    /**
     * @param string $name
     * @param array $runArgs
     */
    public function run(string $name, array $runArgs): void
    {
        if ($this->isScriptName($name)) {
            $this->runScriptByName($name, $runArgs);
        } elseif ($this->isScriptFile($name)) {
            $this->runScriptFile($name, $runArgs);
        } else {
            throw new InvalidArgumentException("invalid script command or script file. (name: $name)");
        }
    }

    /**
     * @param string $scriptExpr
     */
    public function runInputScript(string $scriptExpr): void
    {
        $this->executeScript($scriptExpr, true);
    }

    /**
     * run script by name
     *
     * @param string $name
     * @param array $runArgs
     */
    public function runScriptByName(string $name, array $runArgs): void
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
     * @param string $scriptFile
     * @param array $runArgs
     */
    public function runScriptFile(string $scriptFile, array $runArgs): void
    {
        if (!is_file($scriptFile)) {
            throw new InvalidArgumentException("The script file:$scriptFile not exists");
        }

        $binName  = $workdir = '';
        $filename = basename($scriptFile);
        $extName  = File::getExtension($filename);

        if (!$this->isAllowedExt($extName)) {
            throw new InvalidArgumentException("The script file ext '$extName' is not allowed");
        }

        // #!/usr/bin/env bash
        // #!/usr/bin/bash
        $line = File::readFirstLine($scriptFile);

        // must start withs '#!'
        if (!$line || !str_starts_with($line, '#!')) {
            $command = $scriptFile;

            // auto use bin by file ext.
            if ($this->autoScriptBin) {
                if (isset($this->scriptExt2bin[$extName])) {
                    $binName = $this->scriptExt2bin[$extName];
                    $command = "$binName $scriptFile";
                } else {
                    $binName = ltrim($extName, '.');
                }
            }

            Cli::colored("will direct run the script file: $filename(bin: $binName)", 'cyan');
        } else {
            Cli::colored("will run the script file: $filename (shebang: $line)", 'cyan');

            // eg:
            // '#!/usr/bin/env bash'
            // '#!/usr/bin/env -S gop run'
            if (strpos($line, ' ') > 0) {
                [, $binName] = explode(' ', $line, 2);
                if (str_starts_with($binName, '-S ')) {
                    $binName = substr($binName, 3);
                }
            } else { // eg: '#!/usr/bin/bash'
                $binName = trim($line, '#!');
            }

            // eg: "bash hello.sh"
            $command = "$binName $scriptFile";
        }

        // java need compile TODO use host export logic
        if ($binName === 'java') {
            $workdir   = dirname($scriptFile);
            $className = substr($filename, 0, -5);
            $command   = "javac -encoding UTF-8 $filename; java $className";
        } elseif ($binName === 'groovy') {
            $fileDir = dirname($scriptFile);
            $command = "$binName --classpath $fileDir $scriptFile";
        }

        if ($runArgs) {
            $command .= ' ' . implode(' ', $runArgs);
        }

        $this->fire(self::EVT_ON_RUN_BEFORE, $binName, $runArgs);
        // not in phar.
        if ($binName === 'php' && !KiteUtil::isInPhar()) {
            OS::setEnvVar('KITE_PATH', Kite::basePath());
            OS::setEnvVar('KITE_BOOT_FILE', Kite::getPath('app/boot.php'));
        }

        $this->executeScript($command, false, $workdir);
    }

    /**
     * @param string $name
     * @param array $runArgs
     * @param mixed $commands
     */
    private function executeScripts(string $name, array $runArgs, mixed $commands): void
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
     * @param array $scriptArgs
     *
     * @return string
     */
    private function replaceScriptVars(string $name, string $cmdString, array $scriptArgs): string
    {
        if (!str_contains($cmdString, '$')) {
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
     * @param string $command
     * @param bool $onlyOne
     * @param string $workdir
     */
    private function executeScript(string $command, bool $onlyOne = false, string $workdir = ''): void
    {
        // CmdRunner::new($command)->do(true);
        if ($this->dryRun) {
            Cli::colored('DRY-RUN: ' . $command, 'cyan');
        } else {
            $this->errCode = SysCmd::quickExec($command, $workdir);
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
        if (is_file($name)) {
            return $name;
        }

        $ext = File::getExtension($name);
        if (!$ext || !in_array($ext, $this->scriptExts, true)) {
            return '';
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
        // foreach ($this->scriptDirs as $scriptDir) {
        // $iter = Dir::getIterator($scriptDir);
        // $files = Dir::getFiles($scriptDir, $extMatch);
        // }
    }

    /**
     * @param string $keyword
     *
     * @return array
     */
    public function getAllScriptFiles(string $keyword = ''): array
    {
        $extMatch = implode('|', array_map(static fn($ext) => trim($ext, '.'), $this->scriptExts));

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
            $files = array_filter($files, static fn($file) => stripos($file, $keyword) !== false);
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
            if (str_contains($name, $kw)) {
                $matched[$name] = $item;
            } else {
                $itemString = is_scalar($item) ? (string)$item : Json::encode($item);

                if (str_contains($itemString, $kw)) {
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
     * @return mixed
     */
    public function getScript(string $name): mixed
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
    public function setAutoScriptBin(bool|string $autoScriptBin): void
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
    public function setEnable(bool|int $enable): void
    {
        $this->enable = (bool)$enable;
    }

    /**
     * @return int
     */
    public function getErrCode(): int
    {
        return $this->errCode;
    }

}
