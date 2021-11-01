<?php declare(strict_types=1);

namespace Inhere\Kite\Lib\Parser;

use Inhere\Kite\Lib\Stream\ListStream;
use Inhere\Kite\Lib\Stream\MapStream;
use Inhere\Kite\Lib\Stream\StringsStream;
use function stripos;

/**
 * class DBTable
 */
class DBTable
{
    public const FIELD_META = [
        'name'    => '',
        'type'    => '',
        'typeExt'  => '', // eg: UNSIGNED
        'nullable' => true,
        'default' => '',
        'comment' => '',
    ];

    public string $tableName = '';

    /**
     * table comments desc
     *
     * @var string
     */
    public string $tableComment = '';

    /**
     * @see FIELD_META
     * @var array<string, array>
     */
    public array $fields = [];

    /**
     * @var array
     */
    public array $indexes = [];

    /**
     * @return array
     */
    public function getFieldsComments(): array
    {
        return MapStream::new($this->fields)
            ->eachTo(function (array $meta) {
                return $meta['comment'];
            }, MapStream::new([]))
            ->toArray();
    }

    /**
     * @param string $upperType
     *
     * @return bool
     */
    protected function isNoDefault(string $upperType): bool
    {
        if ($upperType === 'JSON') {
            return true;
        }

        return false;
    }

    /**
     * is index setting line
     *
     * @param string $row
     *
     * @return boolean
     */
    protected function isIndexLine(string $row): bool
    {
        if (str_starts_with($row, '`')) {
            return false;
        }

        if (stripos($row, 'PRIMARY KEY') === 0) {
            return true;
        }

        if (stripos($row, 'UNIQUE KEY') === 0) {
            return true;
        }

        if (stripos($row, 'INDEX KEY') === 0) {
            return true;
        }

        if (stripos($row, 'KEY ') === 0) {
            return true;
        }

        return false;
    }

}
