<?php declare(strict_types=1);

namespace Inhere\Kite\Lib\Parser\MySQL;

use Inhere\Kite\Lib\Parser\Item\FieldItem;

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
