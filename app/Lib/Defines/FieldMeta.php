<?php declare(strict_types=1);

namespace Inhere\Kite\Lib\Defines;

use Inhere\Kite\Lib\Defines\DataType\JavaType;
use Inhere\Kite\Lib\Defines\DataType\UniType;
use InvalidArgumentException;
use RuntimeException;
use Toolkit\Stdlib\Json;
use Toolkit\Stdlib\Obj\BaseObject;
use Toolkit\Stdlib\Str;

/**
 * Field Metadata
 *
 * @author inhere
 */
class FieldMeta extends BaseObject
{
    /**
     * @var string field name
     */
    public string $name = '';

    /**
     * field data type.
     *  - the value is different in different languages.
     *
     * @var string
     */
    public string $type = UniType::STRING;

    /**
     * sub-elem type on type is array, map, object.
     *
     * @var string
     */
    public string $subType = '';

    /**
     * @var string field description(first valid line comment)
     */
    public string $comment = '';

    /**
     * @var string[] field comment lines
     */
    public array $comments = [];

    /**
     * @var string field example value.
     */
    public string $example = '';

    /**
     * @var FieldMeta|null sub-elem field meta on type is array, map, object
     */
    public ?FieldMeta $elem = null;

    /** @var string universal type */
    private string $_uniformType = '';

    /**
     * @return string
     */
    public function uniType(): string
    {
        return $this->getUniType();
    }

    /**
     * get universal type
     *
     * @return string
     */
    public function getUniType(): string
    {
        // in case of child class
        if (static::class !== self::class) {
            if (!$this->_uniformType) {
                $this->_uniformType = $this->toUniType();
            }
            return $this->_uniformType;
        }

        return $this->type;
    }

    protected function toUniType(): string
    {
        throw new RuntimeException('please implement in child class');
    }

    /**
     * @param string $lang distribution language
     * @param string $name field name. if type is object, name is class name.
     *
     * @return string
     */
    public function langType(string $lang, string $name = ''): string
    {
        $name = $name ?: $this->name;
        $uType = $this->getUniType();

        return match ($lang) {
            ProgramLang::PHP => $this->toPhpType($uType, $name),
            ProgramLang::JAVA => $this->toJavaType($uType, $name),
            ProgramLang::GO => $this->toGoType($uType, $name),
            default => throw new InvalidArgumentException('unknown language: ' . $lang),
        };
    }

    private function toGoType(string $uType, string $name = ''): string
    {
        return $uType;
    }

    private function toPhpType(string $uType, string $name = ''): string
    {
        return $uType;
    }

    /**
     * @param string $uType uniform type
     * @param string $name field name
     *
     * @return string
     */
    public function toJavaType(string $uType, string $name = ''): string
    {
        if ($uType === UniType::ARRAY) {
            $elemType = $this->subType ?: $name . '_KW';
            return sprintf('%s<%s>', JavaType::LIST, Str::upFirst($elemType));
        }

        return JavaType::fromUniType($uType);
    }

    /**
     * @return string|int|float return zero value by field type
     */
    public function zeroValue(): string|int|float
    {
        $uType = $this->getUniType();
        if ($uType === UniType::STRING) {
            return '';
        }

        if (UniType::isAnyInt($uType)) {
            return 0;
        }
        if (UniType::isFloat($uType)) {
            return 0.0;
        }

        if ($uType === UniType::ARRAY) {
            return '[]';
        }

        // as object
        return '{}';
    }

    /**
     * @return string|int|float return example value by field type
     */
    public function exampleValue(bool $quote = false): string|int|float
    {
        if ($this->example !== '') {
            $value = $this->example;
        } elseif ($this->isUniType(UniType::STRING)) {
            $value = $this->type;
        } else {
            $value = $this->zeroValue();
        }

        return $quote ? $this->quoteValue($value) : $value;
    }

    /**
     * @return mixed return fake value by field type
     */
    public function fakeValue(bool $quote = false): mixed
    {
        // TODO: implement
        return '';
    }

    public function quoteValue(mixed $value): string
    {
        if ($this->isNeedQuote()) {
            return '"' . $value . '"';
        }

        return (string)$value;
    }

    /**
     * @return bool
     */
    public function isComplexType(): bool
    {
        return UniType::isComplex($this->getUniType());
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
            'example' => $this->example,
            'subType' => $this->subType,
        ];
    }

    /**
     * @return bool is any int type(int, uint, int64, uint64)
     */
    public function isInt(): bool
    {
        return Str::icontains($this->getUniType(), UniType::INT);
    }

    /**
     * @return bool
     */
    public function isIntX(): bool
    {
        return in_array($this->getUniType(), [UniType::INT, UniType::UINT], true);
    }

    /**
     * @return bool
     */
    public function isInt64X(): bool
    {
        return in_array($this->getUniType(), [UniType::INT64, UniType::UINT64], true);
    }

    /**
     * @param string $type
     *
     * @return bool
     */
    public function isUniType(string $type): bool
    {
        return $type === $this->getUniType();
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
     * @return bool
     */
    public function isNeedQuote(): bool
    {
        $uType = $this->getUniType();
        if ($uType === UniType::STRING) {
            return true;
        }

        // if (UniType::isNumber($uType)) {
        //     return false;
        // }
        return false;
    }

    /**
     * @param string $type
     *
     * @return void
     */
    public function setType(string $type): void
    {
        $this->type = $type;
    }

    /**
     * @param string $comment
     *
     * @return void
     */
    public function addComment(string $comment): void
    {
        $s = trim($comment, "/* \t");
        if ($s) {
            if (!$this->comment) {
                $this->comment = $s;
            } else {
                $marks = ['@example', 'example:', 'eg:', 'e.g'];
                if (Str::hasPrefixes($s, $marks)) {
                    $this->example = trim(Str::removePrefixes($s, $marks));
                }
            }
        }

        $this->comments[] = $comment;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return Json::encodeCN($this->toArray());
    }

}