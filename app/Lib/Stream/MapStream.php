<?php declare(strict_types=1);

namespace Inhere\Kite\Lib\Stream;

use function implode;

/**
 * class ListStream
 */
class MapStream extends BaseStream
{
    /**
     * @param array[] $data
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
        foreach ($this as $key => $str) {
            // $new->append($func($str));
            $new->offsetSet($key, $func($str));
        }

        return $new;
    }

    /**
     * @param callable(array): string $func
     *
     * @return $this
     */
    public function eachTo(callable $func, BaseStream $new): BaseStream
    {
        foreach ($this as $key => $item) {
            // $new->append($func($str));
            $item = $func($item, $key);
            $new->offsetSet($key, $item);
        }

        return $new;
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
        foreach ($this as $key => $str) {
            if ($func($str)) {
                // $new->append($str);
                $new->offsetSet($key, $func($str));
            }
        }

        return $new;
    }

    /**
     * @param string $sep
     *
     * @return string
     */
    public function joinValues(string $sep = ','): string
    {
        return $this->implodeValues($sep);
    }

    /**
     * @param string $sep
     *
     * @return string
     */
    public function implodeValues(string $sep = ','): string
    {
        return implode($sep, $this->getArrayCopy());
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return $this->getArrayCopy();
    }
}
