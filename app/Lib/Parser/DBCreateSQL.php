<?php declare(strict_types=1);

namespace Inhere\Kite\Lib\Parser;

use function array_merge;
use function array_pop;
use function array_shift;
use function explode;
use function implode;
use function sprintf;
use function str_replace;
use function stripos;
use function strpos;
use function strtoupper;
use function substr;
use function trim;
use function ucfirst;

/**
 * class DBCreateSQL
 */
class DBCreateSQL extends DBTable
{
    private string $source = '';

    /**
     * @param string $source
     *
     * @return $this
     */
    public function parse(string $source): self
    {
        # code...
        $tableRows = explode("\n", trim($source));
        $tableName = trim(array_shift($tableRows), '( ');
        $tableName = trim(substr($tableName, 12));

        $tableComment = '';
        $tableEngine  = array_pop($tableRows);
        if (($pos = stripos($tableEngine, ' comment')) !== false) {
            $tableComment = trim(substr($tableEngine, $pos + 9), ';\'"');
        }

        $this->tableComment = $tableComment;

        $indexes = [];
        foreach ($tableRows as $row) {
            $row = trim($row, ', ');
            if (!$row) {
                continue;
            }

            if ($this->isIndexLine($row)) {
                $indexes[] = $row;
                continue;
            }

            [$field, $other] = explode(' ', $row, 2);
            [$type, $other] = explode(' ', trim($other), 2);

            if (($pos = stripos($other, 'comment ')) !== false) {
                $comment = trim(substr($other, $pos + 9), '\'"');
                $other   = substr($other, 0, $pos);
            } else {
                $comment = ucfirst(str_replace('_', ' ', $field));
            }

            $field = trim($field, '`');
            $upType = strtoupper($type);
            $isInt  = stripos($type, 'int') !== false;

            $typeExt = '';
            $upOther = strtoupper($other);
            if ($isInt && str_contains($upOther, 'UNSIGNED ')) {
                $typeExt = 'UNSIGNED';
            }

            $allowNull = true;
            if (str_contains($other, 'NOT NULL')) {
                $allowNull = false;
            }

            $default = '';
            if (($pos = strpos($other, 'DEFAULT ')) !== false) {
                $default = trim(substr($other, $pos + 8), '\'" ');
            }

            $this->fields[$field] = array_merge(self::FIELD_META, [
                'name'     => $field,
                'type'     => $upType,
                'typeExt'  => $typeExt,
                'nullable' => $allowNull,
                'default'  => $default,
                'comment'  => $comment,
            ]);

        }

        $this->indexes  = $indexes;
        $this->tableName = $tableName;
        return $this;
    }

    /**
     * @return string
     */
    public function genMDTable(): string
    {
        $mdNodes = [
            ' 字段名 | 类型 | 是否为空 | 默认值 | 注释 ',
            '-------|------|---------|--------|-----'
        ];

        $fmtLines = [];
        foreach ($this->fields as $field => $meta) {
            $upType = $meta['type'];
            if ($meta['typeExt']) {
                $upType .= ' ' . $meta['typeExt'];
            }

            $allowNull = $meta['nullable'] ? 'Yes' : 'No';
            $default = $meta['default'];

            $mdNodes[] = sprintf(
                '`%s` | `%s` | `%s` | %s | %s',
                $field,
                $upType,
                $allowNull,
                $default !== '' ? '`' . $default . '`' : '',
                $meta['comment']
            );
        }

        $fmtLines[] = sprintf("### %s %s\n", $this->tableComment, $this->tableName);
        $fmtLines[] = implode("\n", $mdNodes);

        return implode("\n", $fmtLines) . "\n";
    }

    /**
     * @return string
     */
    public function getSource(): string
    {
        return $this->source;
    }
}
