<?php declare(strict_types=1);
/**
 * This file is part of Kite.
 *
 * @link     https://github.com/inhere
 * @author   https://github.com/inhere
 * @license  MIT
 */

namespace Inhere\Kite\Console\Controller;

use Inhere\Console\Controller;
use Inhere\Console\IO\Input;
use Inhere\Console\IO\Output;
use Inhere\Kite\Console\Component\ContentsAutoReader;
use Inhere\Kite\Lib\Parser\DBTable;
use Inhere\Kite\Lib\Parser\MySQL\TableField;
use Toolkit\PFlag\FlagsParser;
use function file_get_contents;

/**
 * Class JavaController
 */
class JavaController extends Controller
{
    protected static string $name = 'java';

    protected static string $desc = 'Some useful tool commands for java development';

    protected static function commandAliases(): array
    {
        return [
            'json2class' => ['json2c', 'j2c'],
            'sql2class'  => ['sql2c', 's2c'],
        ];
    }

    /**
     * convert JSON/JSON5 contents to JAVA entity,dto class
     *
     * @options
     *  -s,--source       The source code file or contents. if input '@', will read from clipboard
     *  -o,--output     The output target. default is stdout;;stdout
     *
     * @param Output $output
     */
    public function json2classCommand(Output $output): void
    {
        $output->success('TODO');
    }

    /**
     * convert create mysql table SQL to JAVA entity,dto class
     *
     * @options
     *  -s,--source       The source code file or contents. if input '@', will read from clipboard
     *  -o,--output     The output target. default is stdout;;stdout
     *
     * @param FlagsParser $fs
     * @param Output $output
     */
    public function sql2classCommand(FlagsParser $fs, Output $output): void
    {
        $source = $fs->getOpt('source');
        $source = ContentsAutoReader::readFrom($source);

        $dbt = DBTable::fromSchemeSQL($source);
        $mp  = $dbt->getObjFields(TableField::class);

        $output->success('TODO');
    }

    /**
     * Search java package from maven-repo
     *
     * @param Output $output
     */
    public function pkgSearch(Output $output): void
    {
        $output->success('TODO');
    }

    /**
     * List all packages from of the project. from go.mod
     *
     * @param Input  $input
     * @param Output $output
     */
    public function pkgListCommand(Input $input, Output $output): void
    {
        $filepath = $input->getWorkDir() . '/go.mod';
        $content = file_get_contents($filepath);

        echo $content, "\n";

        $output->success('OK');
    }

}
