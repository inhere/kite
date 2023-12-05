<?php declare(strict_types=1);

namespace Inhere\Kite\Lib\Parser\MySQL;

use Inhere\Kite\Lib\Generate\Java\JavaType;
use Inhere\Kite\Lib\Parser\Item\FieldItem;
use Toolkit\Stdlib\Str;
use function sprintf;

/**
 * class TableField
 *
 * @author inhere
 */
class TableField extends FieldItem
{
    /**
     * eg: 10
     *
     * @var int
     */
    public int $typeLen = 0;

    /**
     * eg: UNSIGNED
     *
     * @var string
     */
    public string $typeExt = '';

    /**
     * @var bool
     */
    public bool $allowNull = true;

    /**
     * - use string 'NULL' mark null
     *
     * @var string
     */
    public string $default = '';

    /**
     * @return string
     */
    public function phpType(): string
    {
        return DBType::toPhpType($this->type);
    }

    /**
     * @param string $type DB type
     * @param string $name
     *
     * @return string
     */
    public function toJavaType(string $type, string $name): string
    {
        if ($type === DBType::JSON) {
            return Str::upFirst($name);
        }

        $phpType = DBType::toPhpType($type);
        return parent::toJavaType($phpType, $name);
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        $map = parent::toArray();

        $map['typeLen']   = $this->typeLen;
        $map['typeExt']   = $this->typeExt;
        $map['allowNull'] = $this->allowNull;
        $map['default']   = $this->default;
        return $map;
    }
}
