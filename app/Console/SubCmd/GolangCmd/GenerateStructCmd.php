<?php declare(strict_types=1);

namespace Inhere\Kite\Console\SubCmd\GolangCmd;

use Inhere\Console\Command;
use Inhere\Console\IO\Input;
use Inhere\Console\IO\Output;
use Inhere\Kite\Console\Component\ContentsAutoReader;
use Inhere\Kite\Console\Component\ContentsAutoWriter;
use Inhere\Kite\Kite;
use Inhere\Kite\Lib\Defines\DataField\JsonField;
use Inhere\Kite\Lib\Defines\ProgramLang;
use Inhere\Kite\Lib\Generate\DTOGenerator;
use Inhere\Kite\Lib\Parser\Text\Json5ItemParser;
use Inhere\Kite\Lib\Parser\Text\TextItemParser;
use Inhere\Kite\Lib\Parser\Text\TextParser;
use InvalidArgumentException;
use PhpPkg\Config\ConfigUtil;
use Toolkit\PFlag\FlagsParser;
use Toolkit\PFlag\FlagType;
use Toolkit\PFlag\Validator\EnumValidator;
use Toolkit\Stdlib\Json;
use Toolkit\Stdlib\Str;
use Toolkit\Stdlib\Util\Stream\ListStream;
use function array_filter;
use function array_merge;
use function get_class;
use function println;
use function str_replace;

/**
 * class GenerateStructCmd
 *
 * @author inhere
 */
class GenerateStructCmd extends Command
{
    protected static string $name = 'struct';
    protected static string $desc = 'quick generate an go struct by json, yaml, text and more';

    public static function aliases(): array
    {
        return ['st'];
    }

    protected function configFlags(FlagsParser $fs): void
    {
        $fs->addOptsByRules([
            '-n, --name'              => 'The struct name',
            '--tpl-dir'               => 'The tpl dir',
            '--tpl-file'              => 'The tpl file name',
            '--cols, --fields'        => 'The field names string, split by ","',
            '--get-cols, --only-cols' => 'Only get the provide index cols, start is 0. eg: 1,5',
            '-o, --output'            => 'The output target. default is stdout.',
            '--of, --out-fmt'         => 'The output format. allow: raw, md-table',
            '--is, --item-sep'        => 'The item sep char. default is NL(newline).',
            '--vn, --value-num'       => 'int;set the item value number(cols number), default get from first item.',
            '--vs, --value-sep'       => 'The item value sep char for "space" parser. default is SPACE',
            '-t, --type, --parser'    => [
                'type'      => FlagType::STRING,
                'desc'      => 'The item parser name for difference data type.
TYPE:
  space, text -  parser substr by space, use for text data.
  json, json5 -  parser json(5) line
  yaml, yml   -  convert to json then parse.
',
                'default'   => 'text',
                'validator' => EnumValidator::new(['json', 'json5', 'text', 'space', 'yaml', 'yml'])
            ]
        ]);

        $fs->addOptByRule('s, source', 'The source json,text contents. allow: FILEPATH, @clipboard, @stdin');
    }

    /**
     * Do execute command
     *
     * @param Input $input
     * @param Output $output
     *
     * @return void
     */
    protected function execute(Input $input, Output $output): void
    {
        $fs = $this->flags;

        $source = $this->flags->getOpt('source');
        $source = ContentsAutoReader::readFrom($source);

        $indexes   = [];
        $idxString = $fs->getOpt('only-cols');
        if ($idxString && !$indexes = Str::toInts($idxString)) {
            throw new InvalidArgumentException('please provide valid column index string.');
        }

        $p = TextParser::new($source);
        $p->setItemSep($fs->getOpt('item-sep'));
        $p->setFieldNum($fs->getOpt('value-num'));

        if ($nameString = $fs->getOpt('fields')) {
            $p->setFields(Str::explode($nameString, ','));
        }

        switch ($fs->getOpt('parser')) {
            case 'yml':
            case 'yaml':
                $data = ConfigUtil::parseYamlString($source);
                $p->setText(Json::pretty($data));
                $output->colored('JSON from YAML:');
                println($p->getText());
                $itemParser = new Json5ItemParser;
                $itemParser->setKeyField('name');
                break;
            case 'json':
            case 'json5':
                $itemParser = new Json5ItemParser;
                $itemParser->setKeyField('name');
                break;
            case 'text':
            case 'space':
            default:
                $valueSep   = $fs->getOpt('value-sep', ' ');
                $itemParser = TextItemParser::new($valueSep, $indexes);
                break;
        }

        $p->setItemParser($itemParser);
        $p->parse();
        $data = $p->getData(true);
        // $output->aList($data, 'parsed field list');
        if (!$data) {
            throw new InvalidArgumentException('no field data collected from the source.');
        }

        $lang = ProgramLang::GO;

        $config = Kite::config()->getArray('gen_code');
        $tplDir = $fs->getOpt('tpl-dir', $config['tplDir'] ?? '');
        $tplDir = str_replace('{type}', $lang, $tplDir);

        $tplFile = $fs->getOpt('tpl-file');
        if (!$tplFile) {
            $tplFile = "$tplDir/dto.tpl";
        }

        $config = array_merge($config, array_filter([
            'lang'    =>  $lang,
            'tplDir'  => $tplDir,
            'tplFile' => $tplFile,
            'parser'  => get_class($itemParser),
        ]));

        $output->aList($config);

        $gen = DTOGenerator::new()
            ->setClassName($fs->getOpt('name'))
            ->addTplVar('genMark', $input->getFullScript(true))
            ->configThis($config)
            ->setFields(ListStream::new($data)->eachToArray(function (array $item) {
                return JsonField::new($item);
            }))
            ->setPathResolver([Kite::class, 'resolve']);

        $output->aList($gen->getFields(), 'field list');

        $result  = $gen->generate();
        $outFile = $fs->getOpt('output');
        $output->colored('------------------ Generated Codes -------------------');

        ContentsAutoWriter::writeTo($outFile, $result);
    }
}
