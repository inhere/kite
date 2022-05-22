<?php declare(strict_types=1);

namespace Inhere\Kite\Lib\Parser\Item;

use Inhere\Kite\Lib\Generate\Java\JavaType;
use Inhere\Kite\Lib\Generate\LangName;
use JsonSerializable;
use Toolkit\Stdlib\Json;
use Toolkit\Stdlib\Obj\AbstractObj;
use Toolkit\Stdlib\Str;
use Toolkit\Stdlib\Type;
use function in_array;
use function preg_match;
use function sprintf;

/**
 * class FieldItem
 *
 * @author inhere
 */
class FieldItem extends AbstractObj implements JsonSerializable
{
    /**
     * @var string
     */
    public string $name = '';

    /**
     * @var string
     */
    public string $type = '';

    /**
     * sub-elem type on type is array
     *
     * @var string
     */
    public string $subType = '';

    /**
     * @var string
     */
    public string $comment = '';

    /**
     * @param string $lang
     *
     * @return string
     */
    public function getType(string $lang = LangName::PHP): string
    {
        if ($lang === LangName::PHP) {
            return $this->phpType();
        }

        if ($lang === LangName::JAVA) {
            return $this->javaType();
        }

        if ($lang === LangName::GO) {
            return $this->golangType();
        }

        return $this->type;
    }

    /**
     * @return string
     */
    public function golangType(): string
    {
        if ($this->type === 'long') {
            return 'int64';
        }

        return $this->type;
    }

    /**
     * @return string
     */
    public function phpType(): string
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function javaType(): string
    {
        return $this->toJavaType($this->type, $this->name);
    }

    /**
     * @param string $type
     * @param string $name
     *
     * @return string
     */
    public function toJavaType(string $type, string $name): string
    {
        if (Str::hasSuffixIC($this->name, 'id')) {
            return JavaType::LONG;
        }

        if ($type === Type::ARRAY) {
            $elemType = $this->subType ?: $name;
            if ($elemType === 'List') {
                $elemType .= '_KW';
            }

            return sprintf('%s<%s>', JavaType::LIST, Str::upFirst($elemType));
        }

        if ($type === Type::OBJECT) {
            return Str::upFirst($name);
        }

        return Str::upFirst($type);
    }

    /**
     * @return bool
     */
    public function isComplexType(): bool
    {
        return in_array($this->type, [Type::ARRAY, Type::OBJECT], true);
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
            'name'    => $this->name,
            'type'    => $this->type,
            'comment' => $this->comment,
            'subType' => $this->subType,
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
