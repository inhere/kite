<?php declare(strict_types=1);

namespace Inhere\Kite\Lib\Parser;

use function array_filter;
use function array_map;
use function array_values;
use function count;
use function explode;
use function in_array;
use function str_contains;
use function str_replace;
use function stripos;
use function strtolower;
use function strtoupper;
use function substr;
use function trim;
use function ucfirst;

/**
 * class MkDownTable
 */
class DBMdTable
{
    /**
     * @param string $mdTable
     *
     * @return DBTable
     */
    public function parse(string $mdTable): DBTable
    {
        $dbt = new DBTable();
        $dbt->setSource($mdTable);

        $lines = array_values(
            array_filter(
                explode("\n", trim($mdTable))
            )
        );

        // $table = trim($lines[0], '# ');
        [$tableComment, $tableName] = array_filter(explode(' ', trim($lines[0], '`：#\' ')));

        $tableName = trim($tableName, '` ');
        $titleLine = trim($lines[1], '| ');
        $colNumber = count(explode('|', $titleLine));

        $dbt->setTableName($tableName);
        $dbt->setTableComment($tableComment);

        // rm name, title and split line
        unset($lines[0], $lines[1], $lines[2]);

        $indexesLine = '';
        foreach ($lines as $line) {
            if (str_starts_with($line, '> INDEXES')) {
                $indexesLine = substr($line, 10);
                break;
            }

            $meta = $this->parseLine($line);
            $dbt->addField($meta['name'], $meta);
        }

        if (!$indexesLine) {
            $dbt->addIndexExpr('PRIMARY KEY (`id`)');
        } else {
            $idxLines = explode('),', $indexesLine);
            foreach ($idxLines as $k => $idxLine) {
                $idxLine = trim($idxLine);
                if (!isset($idxLines[$k + 1])) {
                    $dbt->addIndexExpr($idxLine);
                    continue;
                }
                $dbt->addIndexExpr($idxLine . ')');
            }
        }

        return $dbt;
    }

    public function parseLine(string $line): array
    {
        // $line eg: `sid` | `INT(11) UNSIGNED` | `No` | `0` | 下单SID
        $line  = str_replace(['（', '）'], ['(', ')'], trim($line, '| '));
        $nodes = array_map(static function ($value) {
            return trim($value, '`\': ');
        }, explode('|', $line));


        $typeLen  = 0;
        $typeExt  = '';
        $typeNode = $nodes[1];

        $field = $nodes[0];
        $isInt = stripos($typeNode, 'int') !== false;

        $typeNode1 = $typeNode;
        if (str_contains($typeNode1, ' ')) {
            [$typeNode1, $typeExt] = explode(' ', $typeNode1);
        }

        if (str_contains($typeNode1, '(')) {
            [$typeNode1, $len] = explode('(', trim($typeNode1, ') '));
            $typeLen = (int)$len;
        }

        $upType = strtoupper($typeNode1);
        if ($upType === 'STRING') {
            $typeLen = 128;
            $upType  = 'VARCHAR';
            // $typeNode = 'VARCHAR(128)';
        } elseif ($upType === 'INT') {
            $typeLen = 11;
            // $typeNode = 'INT(11) UNSIGNED';
        }

        // create_time
        $isTimeField = stripos($field, 'time') !== false;
        if ($isTimeField && $typeLen < 1) {
            $typeLen = 10;
        }

        // 默认值
        $defValue = '';
        if ($field === 'id') {
            $allowNull = 'NOT NULL';
            $defValue  = 'AUTO_INCREMENT';
        } else {
            // 是否为空
            $allowNull = in_array(strtolower($nodes[2]), ['否', 'no', 'n'], true) ? 'NOT NULL' : '';

            // default value
            if (isset($nodes[3])) {
                if (DBTable::isNoDefault($upType)) {
                    $defValue = '';
                } else {
                    $defValue = $isInt ? (int)$nodes[3] : $nodes[3];
                }
            }
        }

        $fieldComment = $nodes[4] ?? ucfirst($field);

        return [
            'name'     => $field,
            'type'     => $upType,
            'typeLen'  => $typeLen,
            'typeExt'  => $typeExt,
            'nullable' => $allowNull === '',
            'default'  => $defValue,
            'comment'  => $fieldComment,
        ];
    }

}
