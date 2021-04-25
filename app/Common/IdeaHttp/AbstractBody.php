<?php declare(strict_types=1);

namespace Inhere\Kite\Common\IdeaHttp;

use Inhere\Kite\Common\MapObject;
use Toolkit\Stdlib\Type;
use function gettype;

/**
 * Class AbstractBody
 *
 * @package Inhere\Kite\Common\IdeaHttp
 */
abstract class AbstractBody extends MapObject
{
    /**
     * @param string $key
     *
     * @return string
     */
    public function getType(string $key): string
    {
        if (!$this->offsetExists($key)) {
            return Type::UNKNOWN;
        }

        $val = $this->offsetGet($key);
        return gettype($val);
    }

    /**
     * @param array $data
     */
    public function override(array $data): void
    {
        $this->exchangeArray($data);
    }

    /**
     * @param array $data
     * @param bool  $override
     */
    public function load(array $data, bool $override = false): void
    {
        if ($override) {
            $this->override($data);
            return;
        }

        foreach ($data as $key => $val) {
            $this->offsetSet($key, $val);
        }
    }

    /**
     * @return string
     */
    abstract public function __toString(): string;
}
