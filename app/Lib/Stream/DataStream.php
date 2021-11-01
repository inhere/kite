<?php declare(strict_types=1);

namespace Inhere\Kite\Lib\Stream;

use ArrayObject;

/**
 * class DataStream
 */
class DataStream extends ArrayObject
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
     * @param callable(string): string $func
     *
     * @return $this
     */
    public function each(callable $func): self
    {

    }

    /**
     * @return string[]
     */
    public function getData(): array
    {
        return $this->getArrayCopy();
    }
}
