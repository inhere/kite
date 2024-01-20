<?php declare(strict_types=1);

namespace Inhere\Kite\Lib\Defines;

use Inhere\Kite\Lib\Defines\DataType\JavaType;
use Inhere\Kite\Lib\Defines\DataType\UniformType;
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
    public string $type = UniformType::STRING;

    /**
     * sub-elem type on type is array
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

    private string $_uniformType = '';

    /**
     * get uniform type
     *
     * @return string
     */
    public function uniformType(): string
    {
        // in case of child class
        if (static::class !== self::class) {
            if (!$this->_uniformType) {
                $this->_uniformType = $this->toUniformType();
            }
            return $this->_uniformType;
        }

        return $this->type;
    }

    protected function toUniformType(): string
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
        $type = $this->type;

        if ($lang === ProgramLang::PHP) {
            return $this->toPhpType($type, $name);
        }

        if ($lang === ProgramLang::JAVA) {
            return $this->toJavaType($type, $name);
        }

        if ($lang === ProgramLang::GO) {
            return $this->toGoType($type, $name);
        }

        throw new InvalidArgumentException('unknown language: ' . $lang);
    }

    private function toGoType(string $type, string $name = ''): string
    {
        return $type;
    }

    private function toPhpType(string $type, string $name = ''): string
    {
        return $type;
    }

    /**
     * @param string $type PHP type
     * @param string $name field name
     *
     * @return string
     */
    public function toJavaType(string $type, string $name = ''): string
    {
        if ($type === UniformType::INT && Str::hasSuffixIC($name, 'id')) {
            return JavaType::LONG;
        }

        if ($type === UniformType::ARRAY) {
            $elemType = $this->subType ?: $name;
            if ($elemType === 'List') {
                $elemType .= '_KW';
            }

            return sprintf('%s<%s>', JavaType::LIST, Str::upFirst($elemType));
        }

        if (Str::hasSuffixIC($name, 'ids')) {
            return sprintf('%s<%s>', JavaType::LIST, JavaType::LONG);
        }

        if ($type === UniformType::OBJECT) {
            return Str::upFirst($name);
        }
        return Str::upFirst($type);
    }

    /**
     * @return string|int|float return zero value by field type
     */
    public function zeroValue(): string|int|float
    {
        $uType = $this->uniformType();
        if ($uType === UniformType::STRING) {
            return '';
        }

        if (UniformType::isAnyInt($uType)) {
            return 0;
        }
        if (UniformType::isFloat($uType)) {
            return 0.0;
        }

        if ($uType === UniformType::ARRAY) {
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
        } elseif ($this->isString()) {
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
        return UniformType::isComplex($this->uniformType());
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
        return Str::icontains($this->uniformType(), UniformType::INT);
    }

    /**
     * @return bool
     */
    public function isIntX(): bool
    {
        return in_array($this->uniformType(), [UniformType::INT, UniformType::UINT], true);
    }

    /**
     * @return bool
     */
    public function isInt64X(): bool
    {
        return in_array($this->uniformType(), [UniformType::INT64, UniformType::UINT64], true);
    }

    /**
     * @return bool
     */
    public function isString(): bool
    {
        return $this->isUniType(UniformType::STRING);
    }

    /**
     * @param string $type
     *
     * @return bool
     */
    public function isUniType(string $type): bool
    {
        return $type === $this->uniformType();
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
        $uType = $this->uniformType();
        if ($uType === UniformType::STRING) {
            return true;
        }

        // if (UniformType::isNumber($uType)) {
        //     return false;
        // }
        return false;
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