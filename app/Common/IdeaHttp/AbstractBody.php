<?php declare(strict_types=1);

namespace Inhere\Kite\Common\IdeaHttp;

use ArrayObject;
use Toolkit\Stdlib\Type;
use function gettype;

/**
 * Class AbstractBody
 *
 * @package Inhere\Kite\Common\IdeaHttp
 */
abstract class AbstractBody extends ArrayObject
{
    /**
     * @param array $data
     *
     * @return static
     */
    public static function new(array $data)
    {
        return new static($data);
    }

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
}
