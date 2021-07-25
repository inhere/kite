<?php declare(strict_types=1);

namespace Inhere\Kite\Common\Jump;

use InvalidArgumentException;
use JsonSerializable;
use RuntimeException;
use Toolkit\Stdlib\Json;
use function array_merge;
use function array_values;
use function file_exists;
use function file_get_contents;
use function file_put_contents;
use function json_encode;
use function md5;
use function stripos;
use function strpos;

/**
 * Class JumpStorage
 *
 * @package Inhere\Kite\Common\Jump
 */
class JumpStorage implements JsonSerializable
{
    /**
     * @var bool
     */
    private $changed = false;

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

        // TODO use merge load data
        $this->namedPaths = $data['named'] ?? [];
        $this->histories  = $data['histories'] ?? [];
    }

    /**
     * @param array $named
     */
    public function loadData(array $named): void
    {
        $this->changed    = true;
        $this->namedPaths = array_merge($this->namedPaths, $named);
    }

    /**
     * @param string $datafile
     */
    public function dumpTo(string $datafile): void
    {
        if (!$this->changed) {
            return;
        }

        $num = (int)file_put_contents($datafile, $this->toString());
        if ($num < 1) {
            throw new RuntimeException('save data to file failure');
        }
    }

    /**
     * @param string $keywords
     *
     * @return string
     */
    public function matchOne(string $keywords): string
    {
        if (isset($this->namedPaths[$keywords])) {
            return $this->namedPaths[$keywords];
        }

        foreach ($this->histories as $path) {
            if (strpos($path, $keywords) !== false) {
                return $path;
            }
        }

        $id = $this->genID($keywords);
        if (!isset($this->histories[$id]) && file_exists($keywords)) {
            $this->changed        = true;
            $this->histories[$id] = $keywords;
            return $keywords;
        }

        return '';
    }

    /**
     * @param string $keywords
     *
     * @return array
     */
    public function matchAll(string $keywords): array
    {
        $result = [];
        foreach ($this->namedPaths as $name => $path) {
            if (stripos($name, $keywords) !== false) {
                $result[] = $path;
            } elseif (stripos($path, $keywords) !== false) {
                $result[] = $path;
            }
        }

        foreach ($this->histories as $path) {
            if (stripos($path, $keywords) !== false) {
                $result[] = $path;
            }
        }

        return $result;
    }

    /**
     * @param int $flag
     */
    public function reset(int $flag = 0): void
    {
        $this->changed = true;

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
        return json_encode($this->toArray());
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
     * @param string $name
     * @param string $path
     */
    public function setNamed(string $name, string $path): void
    {
        $this->namedPaths[$name] = $path;
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
}
