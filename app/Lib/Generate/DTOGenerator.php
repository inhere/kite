<?php declare(strict_types=1);

namespace Inhere\Kite\Lib\Generate;

use Inhere\Kite\Lib\Defines\FieldMeta;
use Inhere\Kite\Lib\Parser\DBTable;
use Inhere\Kite\Lib\Parser\MySQL\TableField;
use Inhere\Kite\Lib\Parser\TextParser;
use InvalidArgumentException;
use PhpPkg\Config\ConfigUtil;
use Toolkit\Stdlib\Helper\Assert;
use Toolkit\Stdlib\Util\Stream\ListStream;

/**
 * @author inhere
 */
class DTOGenerator extends AbstractGenCode
{
    /**
     * Source metadata contents.
     *  - from: json(5)/yaml/SQL/text
     *
     * @var string
     */
    protected string $source = '';

    /**
     * @var string The source type: json(5)/yaml/SQL/text
     */
    protected string $sourceType = '';

    /**
     * @param string $lang dst language
     *
     * @return DTOGenerator
     */
    public static function create(string $lang): DTOGenerator
    {
        return new DTOGenerator(['lang' => $lang]);
    }

    public function prepare(): self
    {
        if ($this->isPrepared()) {
            return $this;
        }

        parent::prepare();

        $this->loadAndParseSource();

        return $this;
    }

    protected function loadAndParseSource(): void
    {
        $source = $this->source;
        if (!$source = trim($source)) {
            throw new InvalidArgumentException('empty source contents for parse and generate');
        }

        $srcType = $this->sourceType;
        if (!$srcType) {
            $srcType = 'txt';
            if (stripos($source, 'create table')) {
                $srcType = 'sql';
            } elseif (str_contains($source, '--|--')) {
                $srcType = 'md';
            }
        }

        $subObjects = [];
        switch ($srcType) {
            case 'txt':
            case 'text':
                $p  = TextParser::new($source)->parse();
                $mp = ListStream::new($p->getData())
                    ->filter(function (array $item) {
                        return count($item) >= 3;
                    })
                    ->eachToMap(function (array $item) {
                        return [
                            $item[0],
                            FieldMeta::new([
                                'name'    => $item[0],
                                'type'    => $item[1],
                                'comment' => $item[2],
                            ]),
                        ];
                    });

                break;
            case 'sql':
                $dbt = DBTable::fromSchemeSQL($source);
                $mp  = $dbt->getObjFields(TableField::class);

                break;
            case 'md':
                $dbt = DBTable::fromMdTable($source);
                $mp  = $dbt->getObjFields(TableField::class);

                // $mp = MapStream::new($dbt->getFields())
                //     ->eachToMap($this->dbTableInfoHandler());
                break;
            case 'json':
            case 'json5':
                $jd = Json5Data::new()->loadFrom($source);
                $mp = $jd->getFields();

                $this->subObjects = $jd->getSubObjects();
                $this->setContexts($jd->getSettings());
                break;
            case 'yml':
            case 'yaml':
                $data = ConfigUtil::parseYamlString($source);
                Assert::arrayHasKey($data, 'fields');
                $mp = ListStream::new($data['fields'])->eachToMap(function (array $item) {
                    return [
                        $item['name'],
                        FieldMeta::new($item),
                    ];
                });

                break;
            default:
                throw new InvalidArgumentException("unsupported source type: $srcType");
        }

        // $mp: ['name' => FieldMeta]
        $this->fields = $mp;
    }

    /**
     * @return string
     */
    public function getSource(): string
    {
        return $this->source;
    }

    /**
     * @param string $source
     *
     * @return self
     */
    public function setSource(string $source): static
    {
        $this->source = $source;
        return $this;
    }

    public function getSourceType(): string
    {
        return $this->sourceType;
    }

    public function setSourceType(string $sourceType): static
    {
        $this->sourceType = $sourceType;
        return $this;
    }

}
