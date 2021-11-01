<?php declare(strict_types=1);

namespace Inhere\Kite\Lib\Convert;

use Swoft\Swlib\HttpClient;
use function explode;
use function implode;
use function sprintf;
use function strtolower;
use function trim;
use function ucfirst;

/**
 * Class SQLMarkdown
 */
class SQLMarkdown
{
    /**
     * @var string
     */
    public string $tableName = '';

    /**
     * field list
     *
     * [
     *  field name => comment
     * ]
     *
     * @var array<string, string>
     */
    private array $fields = [];

    /**
     * @param string $mdTable
     *
     * @return string
     */
    public function toCreateSQL(string $mdTable): string
    {
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

        $this->tableName = $tableName;

        // rm name, title and split line
        unset($lines[0], $lines[1], $lines[2]);

        $tpl = <<<TXT
CREATE TABLE `{{TABLE}}` (
  {{FIELDS}},
  {{INDEXES}}
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='{{COMMENT}}';
TXT;

        $fieldLines = [];
        // var_dump($lines);die;

        $indexes = 'PRIMARY KEY (`id`)';
        foreach ($lines as $line) {
            if (str_starts_with($line, '> INDEXES')) {
                $indexes = substr($line, 9);
                break;
            }

            // $line eg: `sid` | `INT(11) UNSIGNED` | `No` | `0` | 下单SID
            $line  = str_replace(['（', '）'], ['(', ')'], trim($line, '| '));
            $nodes = array_map(static function ($value) {
                return trim($value, '`\': ');
            }, explode('|', $line));

            $field = $nodes[0];
            $type  = $nodes[1];
            $isInt = stripos($type, 'int') !== false;

            $nodes[0] = "  `$field`";

            // create_time
            $isTimeField = stripos($field, 'time') !== false;
            if ($isTimeField) {
                $upType   = 'INT';
                $typeNode = 'INT(10) UNSIGNED';
            } else {
                $upType = $typeNode = strtoupper($type);
                if ($upType === 'STRING') {
                    $typeNode = 'VARCHAR(128)';
                } elseif ($upType === 'INT') {
                    $typeNode = 'INT(11) UNSIGNED';
                }
            }

            $nodes[1] = $typeNode;

            // 默认值
            if ($field === 'id') {
                $nodes[1] = $upType === 'INT' ? 'INT(11) UNSIGNED' : $nodes[1];
                $nodes[3] = 'AUTO_INCREMENT';
                unset($nodes[2]);
            } else {
                // 是否为空
                $allowNull = in_array(strtolower($nodes[2]), ['否', 'No', 'n'], true) ? 'NOT NULL' : '';
                $nodes[2]  = $allowNull;

                // default value
                if (isset($nodes[3])) {
                    if ($this->isNoDefault($upType)) {
                        $nodes[3] = '';
                    } else {
                        $defValue = $isInt ? (int)$nodes[3] : $nodes[3];
                        $nodes[3] = "DEFAULT '$defValue'";
                    }
                }
            }

            $fieldComment = ucfirst($field);
            if (isset($nodes[4])) {
                $fieldComment = $nodes[4];
                // comment
                $nodes[4] = 'COMMENT ' . ($nodes[4] ? "'$nodes[4]'" : "'$field'");
            }

            $this->fields[$field] = $fieldComment;

            $fieldLines[] = implode(' ', $nodes);
        }

        return strtr($tpl, [
            '{{TABLE}}'   => $tableName,
            '{{INDEXES}}' => $indexes,
            '{{FIELDS}}'  => implode(",\n", $fieldLines),
            '{{COMMENT}}' => $tableComment,
        ]);
    }

    /**
     * @param string $createSql Table create SQL.
     *
     * @return string
     */
    public function toMdTable(string $createSql): string
    {
        # code...
        $tableRows = explode("\n", trim($createSql));
        $tableName = trim(array_shift($tableRows), '( ');
        $tableName = trim(substr($tableName, 12));

        $tableComment = '';
        $tableEngine  = array_pop($tableRows);
        if (($pos = stripos($tableEngine, ' comment')) !== false) {
            $tableComment = trim(substr($tableEngine, $pos + 9), ';\'"');
        }

        $mdNodes = [
            ' 字段名 | 类型 | 是否为空 | 默认值 | 注释 ',
            '-------|------|---------|--------|-----'
        ];

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

            $field   = trim($field, '`');
            $upOther = strtoupper($other);

            $upType = strtoupper($type);
            $isInt  = stripos($type, 'int') !== false;
            if ($isInt && str_contains($upOther, 'UNSIGNED ')) {
                $upType .= ' UNSIGNED';
            }

            $allowNull = 'Yes';
            if (str_contains($other, 'NOT NULL')) {
                $allowNull = 'No';
            }

            $default = '';
            if (($pos = strpos($other, 'DEFAULT ')) !== false) {
                $default = trim(substr($other, $pos + 8), '\'" ');
            }

            $this->fields[$field] = $comment;
            $mdNodes[] = sprintf(
                '`%s` | `%s` | `%s` | %s | %s',
                $field,
                $upType,
                $allowNull,
                $default !== '' ? '`' . $default . '`' : '',
                $comment
            );
        }

        $fmtLines = [];

        // $fmtLines[] = "Fields:";
        // $fmtLines[] = implode("\n", $fields);

        $fmtLines[] = "### $tableComment $tableName\n";
        $fmtLines[] = implode("\n", $mdNodes);

        if ($indexes) {
            $fmtLines[] = "\n> INDEXES: " . implode(', ', $indexes);
        }

        $this->tableName = $tableName;
        return implode("\n", $fmtLines) . "\n";
    }

    private function isNoDefault(string $upperType): bool
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
    private function isIndexLine(string $row): bool
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

    /**
     * @return array
     */
    public function getFields(): array
    {
        return $this->fields;
    }

}