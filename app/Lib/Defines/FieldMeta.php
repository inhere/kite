<?php declare(strict_types=1);

namespace Inhere\Kite\Lib\Defines;

use Inhere\Kite\Lib\Defines\DataType\GoType;
use Inhere\Kite\Lib\Defines\DataType\JavaType;
use Inhere\Kite\Lib\Generate\LangName;
use Toolkit\Stdlib\Json;
use Toolkit\Stdlib\Obj\BaseObject;
use Toolkit\Stdlib\Str;
use Toolkit\Stdlib\Type;

/**
 * Field Metadata
 *
 * @author inhere
 */
class FieldMeta extends BaseObject
{
    /**
     * @var string
     */
    public string $name = '';

    /**
     * @var string
     */
    public string $type = 'string';

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
        return match ($this->type) {
            JavaType::LONG => GoType::INT64,
            Type::INTEGER => Type::INT,
            default => $this->type,
        };
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
     * @param string $type PHP type
     * @param string $name
     *
     * @return string
     */
    public function toJavaType(string $type, string $name): string
    {
        if ($type === Type::INTEGER && Str::hasSuffixIC($this->name, 'id')) {
            return JavaType::LONG;
        }

        if ($type === Type::ARRAY) {
            $elemType = $this->subType ?: $name;
            if ($elemType === 'List') {
                $elemType .= '_KW';
            }

            return sprintf('%s<%s>', JavaType::LIST, Str::upFirst($elemType));
        }

        if (Str::hasSuffixIC($this->name, 'ids')) {
            return sprintf('%s<%s>', JavaType::LIST, JavaType::LONG);
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

}