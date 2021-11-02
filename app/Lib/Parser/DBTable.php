<?php declare(strict_types=1);

namespace Inhere\Kite\Lib\Parser;

use Inhere\Kite\Lib\Stream\MapStream;
use Toolkit\Stdlib\Str;
use function array_merge;
use function implode;
use function sprintf;
use function str_contains;
use function strtoupper;

/**
 * class DBTable
 */
class DBTable
{
    public const FIELD_META = [
        'name'     => '',
        'type'     => '', // int
        'typeLen'  => '', // eg: 10
        'typeExt'  => '', // eg: UNSIGNED
        'nullable' => true,
        'default'  => '',
        'comment'  => '',
    ];

    protected string $source = '';

    /**
     * @var string
     */
    protected string $tableName = '';

    /**
     * table comments desc
     *
     * @var string
     */
    protected string $tableComment = '';

    /**
     * @see FIELD_META
     * @var array<string, array>
     */
    protected array $fields = [];

    /**
     * @var array
     */
    protected array $indexes = [];

    /**
     * @param string $mdTable
     *
     * @return static
     */
    public static function fromMdTable(string $mdTable): self
    {
        return (new DBMdTable())->parse($mdTable);
    }

    /**
     * @param string $createSQL
     *
     * @return static
     */
    public static function fromSchemeSQL(string $createSQL): self
    {
        return (new DBSchemeSQL())->parse($createSQL);
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->toCreateSQL();
    }

    /**
     * @return string
     */
    public function toString(): string
    {
        return $this->toCreateSQL();
    }

    /**
     * @param bool $camelName
     *
     * @return array
     */
    public function getFieldsComments(bool $camelName = false): array
    {
        return MapStream::new($this->fields)
            ->eachTo(function (array $meta, string &$key) use ($camelName) {
                if ($camelName) {
                    $key = Str::camelCase($key, false, '_');
                }

                return $meta['comment'];
            }, MapStream::new([]))
            ->toArray();
    }

    /**
     * @param string $field
     * @param array $meta
     *
     * @return $this
     */
    public function addField(string $field, array $meta): self
    {
        $meta['name'] = $field;

        $this->fields[$field] = array_merge(self::FIELD_META, $meta);
        return $this;
    }

    /**
     * @param string $field
     *
     * @return array
     */
    public function getField(string $field): array
    {
        return $this->fields[$field] ?? [];
    }

    /**
     * @param string $field
     * @param string $nodeName
     *
     * @return array
     */
    public function getFieldNode(string $field, string $nodeName): mixed
    {
        if ($this->hasField($field)) {
            return null;
        }

        return $this->fields[$field][$nodeName] ?? null;
    }

    /**
     * @param string $field
     *
     * @return bool
     */
    public function hasField(string $field): bool
    {
        return isset($this->fields[$field]);
    }

    /**
     * @param string $expr
     *
     * @return $this
     */
    public function addIndexExpr(string $expr): self
    {
        $this->indexes[] = $expr;
        return $this;
    }

    /**
     * @return string
     */
    public function toCreateSQL(): string
    {
        $tpl = <<<TXT
CREATE TABLE `{{TABLE}}` (
  {{FIELDS}},
  {{INDEXES}}
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='{{COMMENT}}';
TXT;

        $fieldLines = [];
        foreach ($this->fields as $field => $meta) {
            $type  = $meta['type'];
            $nodes = [
                "`$field`",
            ];

            $typeNode = strtoupper($type);
            if ($meta['typeLen'] > 0) {
                $typeNode .= '(' . $meta['typeLen'] . ')';
            }

            if ($meta['typeExt']) {
                $typeNode .= ' ' . $meta['typeExt'];
            }

            $nodes[] = $typeNode;
            if (!$meta['nullable']) {
                $nodes[] = 'NOT NULL';
            }

            $default = $meta['default'];
            if ($default !== '') {
                if ($default === 'NULL') {
                    $nodes[] = 'DEFAULT NULL';
                } else {
                    $nodes[] = "DEFAULT '$default'";
                }
                // if ($this->isNoDefault($type)) {
                // } else {
            } elseif (!$meta['nullable'] && self::isStringType($type)) {
                $nodes[] = "DEFAULT ''";
            }

            if ($comment = $meta['comment']) {
                $nodes[] = 'COMMENT ' . "'$comment'";
            }

            // `id` int(11) unsigned NOT NULL COMMENT '主键',
            $fieldLines[] = implode(' ', $nodes);
        }

        return strtr($tpl, [
            '{{TABLE}}'   => $this->tableName,
            '{{INDEXES}}' => implode(",\n  ", $this->indexes),
            '{{FIELDS}}'  => implode(",\n  ", $fieldLines),
            '{{COMMENT}}' => $this->tableComment,
        ]);
    }

    /**
     * @return string
     */
    public function toMDTable(): string
    {
        $mdNodes = [
            ' 字段名 | 类型 | 是否为空 | 默认值 | 注释 ',
            '-------|------|---------|--------|-----'
        ];

        $fmtLines = [];
        foreach ($this->fields as $field => $meta) {
            $upType = $meta['type'];
            if ($meta['typeLen'] > 0) {
                $upType .= '(' . $meta['typeLen'] . ')';
            }

            if ($meta['typeExt']) {
                $upType .= ' ' . $meta['typeExt'];
            }

            $allowNull = $meta['nullable'] ? 'Yes' : 'No';
            $default   = $meta['default'];

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

        if ($this->indexes) {
            $fmtLines[] = "\n> INDEXES: " . implode(', ', $this->indexes);
        }

        return implode("\n", $fmtLines) . "\n";
    }

    /**
     * @param string $source
     */
    public function setSource(string $source): void
    {
        $this->source = $source;
    }

    /**
     * @return string
     */
    public function getSource(): string
    {
        return $this->source;
    }

    /**
     * @return array[]
     */
    public function getFields(): array
    {
        return $this->fields;
    }

    /**
     * @return array
     */
    public function getIndexes(): array
    {
        return $this->indexes;
    }

    /**
     * @param array $indexes
     */
    public function setIndexes(array $indexes): void
    {
        $this->indexes = $indexes;
    }

    /**
     * @return string
     */
    public function getTableName(): string
    {
        return $this->tableName;
    }

    /**
     * @param string $tableName
     */
    public function setTableName(string $tableName): void
    {
        $this->tableName = $tableName;
    }

    /**
     * @return string
     */
    public function getTableComment(): string
    {
        return $this->tableComment;
    }

    /**
     * @param string $tableComment
     */
    public function setTableComment(string $tableComment): void
    {
        $this->tableComment = $tableComment;
    }

    /**
     * @param string $upperType
     *
     * @return bool
     */
    public static function isStringType(string $upperType): bool
    {
        if (str_contains($upperType, 'TEXT')) {
            return true;
        }

        if (str_contains($upperType, 'CHAR')) {
            return true;
        }

        return false;
    }

    /**
     * @param string $upperType
     *
     * @return bool
     */
    public static function isNoDefault(string $upperType): bool
    {
        if ($upperType === 'JSON') {
            return true;
        }

        return false;
    }
}
