<?php declare(strict_types=1);

namespace Inhere\Kite\Lib\Stream;

use function implode;

/**
 * class ListStream
 */
class ListStream extends BaseStream
{
    /**
     * @param array $data
     *
     * @return static
     */
    public static function new(array $data): self
    {
        return new self($data);
    }

    /**
     * @param callable(array): string $func
     * @param bool|mixed $apply
     *
     * @return $this
     */
    public function eachIf(callable $func, mixed $apply): self
    {
        if (!$apply) {
            return $this;
        }

        return $this->each($func);
    }

    /**
     * @param callable(array): string $func
     *
     * @return $this
     */
    public function each(callable $func): self
    {
        $new = new self();
        foreach ($this as $item) {
            $new->append($func($item));
        }

        return $new;
    }

    /**
     * @param callable(array): string $func
     * @param BaseStream $new
     *
     * @return BaseStream
     */
    public function eachTo(callable $func, BaseStream $new): BaseStream
    {
        foreach ($this as $item) {
            $new->append($func($item));
            // $new->offsetSet($key, $func($item));
        }

        return $new;
    }

    /**
     * @param callable(array, int): array $func
     *
     * @return array
     */
    public function eachToArray(callable $func): array
    {
        $arr = [];
        foreach ($this as $idx => $item) {
            $arr[] = $func($item, $idx);
        }
        return $arr;
    }

    /**
     * @param callable(array): array $func
     * @param MapStream $new
     *
     * @return MapStream
     */
    public function eachToMapStream(callable $func, MapStream $new): MapStream
    {
        foreach ($this as $item) {
            [$key, $val] = $func($item);
            $new->offsetSet($key, $val);
        }

        return $new;
    }

    /**
     * @param callable(array): array $func
     *
     * @return array<string, mixed>
     */
    public function eachToMap(callable $func): array
    {
        $map = [];
        foreach ($this as $item) {
            [$key, $val] = $func($item);
            $map[$key] = $val;
        }

        return $map;
    }

    /**
     * @param callable(array): bool $func
     * @param bool|mixed $apply
     *
     * @return $this
     */
    public function filterIf(callable $func, mixed $apply): self
    {
        if (!$apply) {
            return $this;
        }

        return $this->filter($func);
    }

    /**
     * @param callable(array): bool $func
     *
     * @return $this
     */
    public function filter(callable $func): self
    {
        $new = new self();
        foreach ($this as $item) {
            if ($func($item)) {
                $new->append($item);
            }
        }

        return $new;
    }

    /**
     * @param string $sep
     *
     * @return string
     */
    public function join(string $sep = ','): string
    {
        return $this->implode($sep);
    }

    /**
     * @param string $sep
     *
     * @return string
     */
    public function implode(string $sep = ','): string
    {
        return implode($sep, $this->getArrayCopy());
    }

    // public function prepend(string $value): self
    // {
    //     return $this;
    // }

    public function append($value): self
    {
        parent::append($value);
        return $this;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return $this->getArrayCopy();
    }
}
