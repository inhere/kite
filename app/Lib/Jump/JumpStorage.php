<?php declare(strict_types=1);

namespace Inhere\Kite\Lib\Jump;

use InvalidArgumentException;
use JsonSerializable;
use RuntimeException;
use Toolkit\FsUtil\Dir;
use Toolkit\FsUtil\FS;
use Toolkit\Stdlib\Json;
use Toolkit\Stdlib\OS;
use function array_merge;
use function array_values;
use function date;
use function dirname;
use function file_exists;
use function file_get_contents;
use function file_put_contents;
use function is_dir;
use function md5;
use function str_replace;
use function stripos;
use function strpos;
use const JSON_PRETTY_PRINT;
use const JSON_UNESCAPED_SLASHES;

/**
 * Class JumpStorage
 *
 * @package Inhere\Kite\Lib\Jump
 */
class JumpStorage implements JsonSerializable
{
    public const MATCH_NAMED = 1;
    public const MATCH_HIST  = 2;
    public const MATCH_BOTH  = 3;

    /**
     * @var string
     */
    private $datafile;

    /**
     * @var bool
     */
    private $dataChanged = false;

    /**
     * @var string
     */
    private $prevPath = '';

    /**
     * @var string
     */
    private $lastPath = '';

    /**
     * Named jump paths.
     *
     * ```php
     * [
     *  name1 => path to 1,
     *  name2 => path to 2,
     *  ...
     * ]
     * ```
     *
     * @var array
     */
    private $namedPaths = [];

    /**
     *
     * ```php
     * [
     *  uni-key1 => path to 1,
     *  uni-key2 => path to 2,
     *  uni-key3 => path to 3,
     *  ...
     * ]
     * ```
     *
     * @var string[]
     */
    private $histories = [];

    /**
     * @var bool
     */
    private $windowsOS;

    /**
     * Class constructor.
     *
     * @param string $datafile
     */
    public function __construct(string $datafile = '')
    {
        $this->datafile = $datafile;
        $this->windowsOS  = OS::isWindows();
    }

    public function init(): void
    {
        if ($this->datafile) {
            $this->loadFile($this->datafile, true);
            // init load not update mark.
            $this->dataChanged = false;
        }
    }

    /**
     * @param string $datafile
     * @param bool   $ignoreNotExist
     */
    public function loadFile(string $datafile, bool $ignoreNotExist = false): void
    {
        if (!file_exists($datafile)) {
            if ($ignoreNotExist) {
                return;
            }

            throw new InvalidArgumentException('the template file is not exist. file:' . $datafile);
        }

        $json = file_get_contents($datafile);
        $data = Json::decode($json, true);

        $this->loadData($data);
    }

    /**
     * @param array $data [namedPaths: [], histories: []]
     */
    public function loadData(array $data): void
    {
        $this->loadNamedPaths($data['namedPaths'] ?? []);
        $this->loadHistories($data['histories'] ?? []);

        if (!empty($data['lastPath'])) {
            $this->lastPath = $this->formatPath($data['lastPath']);
        }

        if (!empty($data['prevPath'])) {
            $this->prevPath = $this->formatPath($data['prevPath']);
        }
    }

    /**
     * @param array $namedPaths
     */
    public function loadNamedPaths(array $namedPaths): void
    {
        foreach ($namedPaths as $name => $path) {
            if (!$name) {
                continue;
            }

            $this->addNamed($name, $path);
        }
    }

    /**
     * @param string $id
     * @param string $path
     * @param bool   $override
     *
     * @return bool
     */
    public function addNamed(string $id, string $path, bool $override = false): bool
    {
        if ($override || !isset($this->namedPaths[$id])) {
            $path = FS::realpath($path);

            if (is_dir($path)) {
                $path = $this->formatPath($path);

                $this->dataChanged     = true;
                $this->namedPaths[$id] = $path;
                return true;
            }
        }

        return false;
    }

    /**
     * @param array $histories
     */
    public function loadHistories(array $histories): void
    {
        foreach ($histories as $path) {
            $this->addHistory($path);
        }
    }

    /**
     * @param string $path
     *
     * @return bool
     */
    public function addHistory(string $path): bool
    {
        $path  = FS::realpath($path);
        $isDir = is_dir($path);

        // fix: completed result not works for jump
        if ($isDir && $this->windowsOS) {
            $path = str_replace('\\', '/', $path);
        }

        // add latest path.
        $ok = false;
        if ($isDir && $this->lastPath !== $path) {
            $this->dataChanged = true;
            $this->prevPath    = $this->lastPath;
            $this->lastPath    = $path;

            $ok = true;
        }

        // add to history
        $id = $this->genID($path);
        if ($isDir && !isset($this->histories[$id])) {
            $this->dataChanged    = true;
            $this->histories[$id] = $path;

            $ok = true;
        }

        return $ok;
    }

    public function dump(): void
    {
        $this->dumpTo($this->datafile);
    }

    /**
     * @param string $datafile
     * @param bool   $force
     */
    public function dumpTo(string $datafile = '', bool $force = false): void
    {
        if (false === $force && false === $this->dataChanged) {
            return;
        }

        $datafile = $datafile ?: $this->datafile;

        // ensure dir is created.
        Dir::mkdir(dirname($datafile));

        $num = (int)file_put_contents($datafile, $this->toString());
        if ($num < 1) {
            throw new RuntimeException('save data to file failure');
        }

        $this->dataChanged = false;
    }

    /**
     * @param string $keywords
     *
     * @return string
     */
    public function matchOne(string $keywords): string
    {
        if (!$keywords || '.' === $keywords) {
            return OS::getWorkDir();
        }

        // latestPath
        if ($keywords === '-') {
            return $this->prevPath;
        }

        if (is_dir($keywords)) {
            return $keywords;
        }

        if (isset($this->namedPaths[$keywords])) {
            return $this->namedPaths[$keywords];
        }

        foreach ($this->histories as $path) {
            if (strpos($path, $keywords) !== false) {
                return $path;
            }
        }

        return '';
    }

    /**
     * @param string $keywords
     * @param int    $flag
     *
     * @return array
     */
    public function matchAll(string $keywords, int $flag = self::MATCH_BOTH): array
    {
        if (!$keywords) {
            return array_merge($this->namedPaths, array_values($this->histories));
        }

        $result = [];
        if ($flag & self::MATCH_NAMED) {
            $result = $this->matchFromNamed($keywords);
        }

        if ($flag & self::MATCH_HIST) {
            foreach ($this->histories as $path) {
                if (stripos($path, $keywords) !== false) {
                    $result[] = $path;
                }
            }
        }

        return $result;
    }

    /**
     * @param string $keywords
     *
     * @return array
     */
    public function matchFromNamed(string $keywords): array
    {
        $result = [];
        foreach ($this->namedPaths as $name => $path) {
            if (stripos($name, $keywords) !== false) {
                $result[$name] = $path;
            } elseif (stripos($path, $keywords) !== false) {
                $result[$name] = $path;
            }
        }
        return $result;
    }

    /**
     * @param int $flag
     */
    public function reset(int $flag = 0): void
    {
        $this->dataChanged = true;

        if ($flag > 1) {
            $this->histories = [];
        } elseif ($flag === 1) {
            $this->namedPaths = [];
        } else {
            $this->histories  = [];
            $this->namedPaths = [];
        }
    }

    /**
     * @param string $path
     *
     * @return string
     */
    public function formatPath(string $path): string
    {
        if ($this->windowsOS) {
            $path = str_replace('\\', '/', $path);
        }

        return $path;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->toString();
    }

    /**
     * @return string
     */
    public function toString(): string
    {
        return Json::encode($this->toArray(), JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    }

    /**
     * @param bool $clearId
     *
     * @return array
     */
    public function toArray(bool $clearId = false): array
    {
        $hs = $clearId ? array_values($this->histories) : $this->histories;

        return [
            'datetime'   => date('Y-m-d H:i:s'),
            'lastPath'   => $this->lastPath,
            'prevPath'   => $this->prevPath,
            'namedPaths' => $this->namedPaths,
            'histories'  => $hs,
        ];
    }

    /**
     * @param string $path
     *
     * @return string
     */
    public function genID(string $path): string
    {
        return md5($path);
    }

    /**
     * Specify data which should be serialized to JSON
     *
     * @link  https://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4
     */
    public function jsonSerialize()
    {
        return $this->toArray();
    }

    /**
     * @return array
     */
    public function getNamedPaths(): array
    {
        return $this->namedPaths;
    }

    /**
     * @return string[]
     */
    public function getHistories(): array
    {
        return $this->histories;
    }

    /**
     * @return string
     */
    public function getDatafile(): string
    {
        return $this->datafile;
    }

    /**
     * @param string $datafile
     */
    public function setDatafile(string $datafile): void
    {
        $this->datafile = $datafile;
    }

    /**
     * @return string
     */
    public function getLastPath(): string
    {
        return $this->lastPath;
    }

    /**
     * @return string
     */
    public function getPrevPath(): string
    {
        return $this->prevPath;
    }
}
