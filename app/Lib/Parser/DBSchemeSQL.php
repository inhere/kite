<?php declare(strict_types=1);

namespace Inhere\Kite\Lib\Parser;

use InvalidArgumentException;
use function array_pop;
use function array_shift;
use function explode;
use function str_replace;
use function stripos;
use function strpos;
use function strtolower;
use function strtoupper;
use function substr;
use function trim;
use function ucfirst;

/**
 * class DBSchemeSQL
 */
class DBSchemeSQL
{
    /**
     * @param string $createSQL
     *
     * @return DBTable
     */
    public static function parseSQL(string $createSQL): DBTable
    {
        return (new self())->parse($createSQL);
    }

    /**
     * @param string $createSQL
     *
     * @return DBTable
     */
    public function parse(string $createSQL): DBTable
    {
        $dbt = new DBTable();
        $dbt->setSource($createSQL);

        if (!$createSQL = trim($createSQL)) {
            throw new InvalidArgumentException('empty create SQL for parse');
        }

        if (stripos($createSQL, 'CREATE TABLE ') !== 0) {
            throw new InvalidArgumentException('invalid create table SQL for parse');
        }

        // match end \s*\)\s*(\w+\s*=.*);
        $createSQL .= ";";

        $tableRows = explode("\n", $createSQL);
        $tableName = trim(array_shift($tableRows), " \t\n\r\0\x0B(");
        $tableName = trim(substr($tableName, 12), " \t\n\r\0\x0B`");

        $tableEngine  = array_pop($tableRows);
        if (stripos($tableEngine, ' comment') !== false) {
            $tableInfo = $this->parseTableMeta($tableEngine);
            $dbt->setTableComment($tableInfo['comment']);
        } else {
            $tableRows[] = $tableEngine;
        }

        $dbt->setTableName($tableName);

        $indexes = [];
        $endStr = '';
        foreach ($tableRows as $row) {
            $row = trim($row, ";, \t\n\r\0\x0B");
            if (!$row) {
                continue;
            }

            // eg: "(", ") ENGINE=some" table info 在多行
            if ($row[0] === '(' || $row[0] === ')' || !str_contains($row, ' ')) {
                $endStr .= ' ' . $row;
                continue;
            }

            if ($this->isIndexLine($row)) {
                $indexes[] = $row;
                continue;
            }

            $meta = $this->parseLine($row);
            $dbt->addField($meta['name'], $meta);
        }

        $dbt->setIndexes($indexes);

        if ($endStr && !$dbt->tableComment) {
            $tableInfo = $this->parseTableMeta($endStr);
            $dbt->setTableComment($tableInfo['comment']);
        }

        return $dbt;
    }

    /**
     * @param string $row
     *
     * @return array
     */
    public function parseLine(string $row): array
    {
        [$field, $other] = explode(' ', $row, 2);
        [$type, $other] = explode(' ', trim($other), 2);

        $field = trim($field, '`');
        $isInt = stripos($type, 'int') !== false;

        $typeLen = 0;
        if (str_contains($type, '(')) {
            [$type, $len] = explode('(', trim($type, ') '));
            $typeLen = (int)$len;
        }

        if (($pos = stripos($other, 'comment ')) !== false) {
            $endPos = stripos($other, 'COLLATE ');
            // vdump($other, $endPos);
            if ($endPos > 0) {
                $comment = trim(substr($other, $pos + 9, $endPos - $pos - 11), '\'"');
            } else {
                $comment = trim(substr($other, $pos + 9), '\'"');
            }

            $other   = substr($other, 0, $pos);
        } else {
            $comment = ucfirst(str_replace('_', ' ', $field));
        }

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

        return [
            'name'     => $field,
            'type'     => strtolower($type),
            'typeLen'  => $typeLen,
            'typeExt'  => $typeExt,
            'allowNull' => $allowNull,
            'default'  => $default,
            'comment'  => $comment,
        ];
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

        if (stripos($row, 'UNIQUE ') === 0) {
            return true;
        }

        if (stripos($row, 'INDEX ') === 0) {
            return true;
        }

        if (stripos($row, 'KEY ') === 0) {
            return true;
        }

        return false;
    }

    protected function parseTableMeta(string $str): array
    {
        $info = [];
        $kvNodes = explode(' ', trim($str, " );\n"));

        foreach ($kvNodes as $value) {
            [$k, $v] = explode('=', $value, 2);

            $info[strtolower($k)] = trim($v, ' "\'');
        }
        return $info;
    }

}
