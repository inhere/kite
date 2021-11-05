<?php declare(strict_types=1);

namespace Inhere\Kite\Lib\Generate\Json;

use JsonSerializable;
use Toolkit\Stdlib\Json;
use Toolkit\Stdlib\Obj\AbstractObj;
use Toolkit\Stdlib\Type;
use function gettype;
use function json_encode;

/**
 * class JsonField
 */
class JsonField extends AbstractObj implements JsonSerializable
{
    public string $name;
    public string $type;
    public string $desc;

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'type' => $this->type,
            'desc' => $this->desc,
        ];
    }

    /**
     * @return bool
     */
    public function isInt(): bool
    {
        return $this->isType(Type::INTEGER);
    }

    /**
     * @return bool
     */
    public function isStr(): bool
    {
        return $this->isType(Type::STRING);
    }

    /**
     * @param string $type
     *
     * @return bool
     */
    public function isType(string $type): bool
    {
        return $type === $this->type;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return Json::encodeCN($this->toArray());
    }

    /**
     * @return array
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
