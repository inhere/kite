<?php declare(strict_types=1);

namespace Inhere\Kite\Lib\Parser\Item;

use Inhere\Kite\Lib\Generate\Java\JavaType;
use JsonSerializable;
use Toolkit\Stdlib\Json;
use Toolkit\Stdlib\Obj\AbstractObj;
use Toolkit\Stdlib\Str;
use Toolkit\Stdlib\Type;
use function preg_match;

/**
 * class FieldItem
 *
 * @author inhere
 */
class FieldItem extends AbstractObj implements JsonSerializable
{
    public string $name;

    public string $type;

    public string $comment = '';

    /**
     * @param string $lang
     *
     * @return string
     */
    public function getType(string $lang = 'php'): string
    {
        if ($lang === 'php') {
            return $this->type;
        }

        return $this->toJavaType();
    }

    /**
     * @return string
     */
    public function toJavaType(): string
    {
        if ($this->type === Type::ARRAY) {
            return JavaType::OBJECT;
        }

        if (str_ends_with($this->name, 'id') || str_ends_with($this->name, 'Id')) {
            return JavaType::LONG;
        }

        return Str::upFirst($this->type);
    }

    /**
     * @return bool
     */
    public function isMultiWords(): bool
    {
        if (str_contains($this->name, '_')) {
            return true;
        }

        if (preg_match('/[A-Z]/', $this->name)) {
            return true;
        }

        return false;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'name'   => $this->name,
            'type'   => $this->type,
            'comment' => $this->comment,
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
