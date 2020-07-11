<?php declare(strict_types=1);

namespace Inhere\Kite\Console\Controller;

use Inhere\Console\Controller;
use Inhere\Console\IO\Input;
use Inhere\Console\IO\Output;
use Toolkit\Stdlib\Str;
use Toolkit\Sys\Proc\ProcWrapper;
use function date;
use function explode;
use function file_get_contents;
use function parse_ini_string;
use function str_replace;
use function trim;

/**
 * Class GenerateGroup
 *
 * @package Inhere\Kite\Console\Group
 */
class GenerateController extends Controller
{
    protected static $name = 'gen';

    protected static $description = 'quick generate new class or file from template';

    public static function aliases(): array
    {
        return ['generate'];
    }

    protected static function commandAliases(): array
    {
        return [
            'rpt' => 'repeat',
            'tpl' => 'template',
        ];
    }

    /**
     * @param Input  $input
     * @param Output $output
     */
    public function readmeCommand(Input $input, Output $output): void
    {
        $output->success('Complete');
    }

    /**
     * @param Input $input
     */
    protected function templateConfigure(Input $input): void
    {
        $input->bindArgument('filename', 0);
    }

    /**
     * Create an template file on runtime dir.
     *
     * @options
     *  --editor    Editor for edit the template file
     *
     * @arguments
     *  filename     The template filename. If not set, will auto generate by datetime.
     *
     * @param Input  $input
     * @param Output $output
     */
    public function templateCommand(Input $input, Output $output): void
    {
        $editor   = $input->getStringOpt('editor', 'vim');
        $filepath = 'runtime/' . date('Ymd_Hi') . '.tpl';

        ProcWrapper::runEditor($editor, $filepath);

        $output->writeln($filepath);
    }

    /**
     * @param Input $input
     */
    protected function repeatConfigure(Input $input): void
    {
        $input->bindArgument('tpl', 0);
    }

    /**
     * Repeat generate some codes by input vars
     *
     * @arguments
     *  tpl         The template filepath
     *
     * @param Input  $input
     * @param Output $output
     */
    public function repeatCommand(Input $input, Output $output): void
    {
        $tplFile = $input->getRequiredArg('tpl');

        $content = file_get_contents($tplFile);

        [$varDefine, $template] = explode('###', $content);

        $vars = (array)parse_ini_string(trim($varDefine), true);

        $template = trim($template);
        $snippets = [''];

        foreach ($vars as $var => $valString) {
            $values = Str::explode($valString, ',');

            // repeat by values
            foreach ($values as $val) {
                $snippets[] = str_replace('{$' . $var . '}', $val, $template);
            }
        }

        $output->success('Complete');
        $output->writeRaw(\implode("\n", $snippets));
    }
}
