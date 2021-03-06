<?php declare(strict_types=1);

namespace Inhere\Kite\Common\IdeaHttp;

use ArrayObject;

/**
 * Class ClientEnvData
 *
 * @package Inhere\Kite\Common\IdeaHttp
 */
final class ClientEnvData extends ArrayObject
{
    /**
     * @param string $key
     * @param mixed  $default
     *
     * @return mixed|null
     */
    public function getValue(string $key, $default = null)
    {
        return $this[$key] ?? $default;
    }

    /**
     * @param string $key
     * @param int    $default
     *
     * @return int
     */
    public function getInteger(string $key, int $default = 0): int
    {
        if ($this->offsetExists($key)) {
            return (int)$this->offsetGet($key);
        }

        return $default;
    }

    /**
     * @param string $key
     * @param string  $default
     *
     * @return string
     */
    public function getString(string $key, string $default = ''): string
    {
        if ($this->offsetExists($key)) {
            return (string)$this->offsetGet($key);
        }

        return $default;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return $this->getArrayCopy();
    }
}
