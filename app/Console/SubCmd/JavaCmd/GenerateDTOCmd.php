<?php declare(strict_types=1);

namespace Inhere\Kite\Console\SubCmd\JavaCmd;

use Inhere\Console\Command;
use Inhere\Console\IO\Input;
use Inhere\Console\IO\Output;
use Inhere\Kite\Console\Component\ContentsAutoReader;
use Inhere\Kite\Console\Component\ContentsAutoWriter;
use Inhere\Kite\Lib\Defines\FieldMeta;
use Inhere\Kite\Lib\Generate\Json5Data;
use Inhere\Kite\Lib\Parser\DBTable;
use Inhere\Kite\Lib\Parser\MySQL\TableField;
use Inhere\Kite\Lib\Parser\Text\TextParser;
use Inhere\Kite\Lib\Stream\ListStream;
use InvalidArgumentException;

/**
 * Class GenerateDTOCmd
 */
class GenerateDTOCmd extends Command
{
    protected static string $name = 'gen-dto';
    protected static string $desc = 'generate DTO class from create SQL/JSON/JSON5 contents';

    public static function aliases(): array
    {
        return ['to-dto', 'dto'];
    }

    protected function configure(): void
    {
        // $this->flags->addOptByRule($name, $rule);
    }

    protected function getOptions(): array
    {
        return [
            '--source, -s'        => 'source create-sql/markdown-table/field-map-txt for parse.
allow:
FILEPATH, @clipboard, @stdin
            ',
            '-t, --source-type'   => 'source contents type, allow: sql,md,txt,json;true',
            '-c, --config'        => 'can load custom config from a file, allow: ini,json,yaml',
            '--show-info'         => 'bool;show config and context for generate',
            '--tpl, --tpl-file'   => 'generate for give tpl file name or path',
            '--ctx'               => 'array;provide context data, format KEY:VALUE',
            '--pkg, --module-pkg' => 'the java DTO class package. eg: com.kezhilian.wzl.service.order',
            '--sub, --with-subs'  => 'bool;on use json data, whether generate sub-objects as inner class',
            '--name'              => 'the DTO class name, not need suffix DTO',
            '--output, -o'        => 'string;the output for write result codes;;stdout',
            // override, append, skip
            '--wflag'             => 'string;write contents flag setting, allow:
a - append, append contents to exists file
o - override, override exists file
s - skip, skip write exists file;;s
             ',
        ];
    }

    /**
     * @options
     *   -s,--source       The source code file or contents. if input '@c', will read from clipboard
     *   -o,--output       The output target. default is stdout;;stdout
     *
     * @param Input  $input
     * @param Output $output
     *
     */
    protected function execute(Input $input, Output $output): void
    {
        $fs = $this->flags;

        $source = $fs->getOpt('source');
        $source = ContentsAutoReader::readFrom($source);

        $this->loadContextData($fs);

        $srcType = $fs->getOpt('source-type');
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
                            ])
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

                $subObjects = $jd->getSubObjects();
                $this->config->load($jd->getSettings());
                break;
            default:
                throw new InvalidArgumentException("unsupported source type: $srcType");
        }

        $mainName = $this->config->getString('name');
        if (!$mainName) {
            $mainName = $fs->getMustOpt('name');
        } elseif ($inName = $fs->getOpt('name')) {
            $mainName = $inName;
        }

        $this->config->set('mainName', lcfirst($mainName));
        $this->config->set('className', ucfirst($mainName));

        $tplFile = $fs->getOpt('tpl-file');
        $tplFile = $tplFile ?: "@custom/template/java-code-tpl/req_dto.tpl";
        $outFile = $fs->getOpt('output');

        if ($fs->getOpt('show-info')) {
            $ctx = $this->config->toArray();

            $ctx['tplFile'] = $tplFile;
            $ctx['outFile'] = $outFile;
            $output->mList([
                'info'   => $ctx,
                'fields' => $mp,
            ]);
            return;
        }

        $tpl = $this->createRenderer();
        $ctx = $this->config->toArray();
        $output->mList([
            'template' => $tplFile,
            'context'  => $ctx,
            'fields'   => $mp,
        ]);

        $ctx['fields'] = $mp;

        $result = $tpl->renderFile($tplFile, $ctx);

        if ($subObjects) {
            $ctx['withHead']  = false;
            $ctx['classSfx']  = '';
            $ctx['classMark'] = 'static ';
            foreach ($subObjects as $name => $fields) {
                $ctx['mainName']  = $name;
                $ctx['className'] = ucfirst($name);
                $ctx['fields']    = $fields;

                $result .= $tpl->renderFile($tplFile, $ctx);
            }
        }

        ContentsAutoWriter::writeTo($outFile, $result);
    }
}
