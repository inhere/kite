<?php declare(strict_types=1);

namespace Inhere\Kite\Console\Controller;

use Inhere\Console\Controller;
use Inhere\Console\Exception\PromptException;
use Inhere\Console\IO\Input;
use Inhere\Console\IO\Output;
use Inhere\Kite\Common\TextTemplate;
use Toolkit\Stdlib\Str;
use Toolkit\Sys\Proc\ProcWrapper;
use function date;
use function explode;
use function file_get_contents;
use function implode;
use function is_string;
use function parse_ini_string;
use function strpos;
use function strtr;
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
        $filepath = 'tmp/' . date('Ymd_Hi') . '.tpl';

        ProcWrapper::runEditor($editor, $filepath);

        $output->writeln($filepath);
    }

    /**
     * @param Input $input
     */
    protected function parseConfigure(Input $input): void
    {
        $input->bindArgument('tpl', 0);
    }

    /**
     * parse and generate some codes by input template file
     *
     * @arguments
     *  tpl         The template filepath
     *
     * @param Input  $input
     * @param Output $output
     */
    public function parseCommand(Input $input, Output $output): void
    {
        $tplFile = $input->getRequiredArg('tpl');
        $content = file_get_contents($tplFile);

        [$varDefine, $template] = explode('###', $content);

        $vars = (array)parse_ini_string(trim($varDefine), true);
        foreach ($vars as $k => $var) {
            if (is_string($var)) {
                $str = trim($var);
                // is array
                if (strpos($str, '[') === 0) {
                    $vars[$k] = Str::explode(trim($str, '[]'), ',');
                }
            }
        }

        $output->aList($vars, 'template vars', ['ucFirst' => false]);

        $logic  = new TextTemplate();
        $result = $logic->renderString(trim($template), $vars);

        $output->success('Render Result:');
        $output->writeRaw($result);
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

        $snippets = [''];
        $template = trim($template);

        $vars = (array)parse_ini_string(trim($varDefine), true);
        if (!isset($vars['copy'])) {
            throw new PromptException('Must contains "copy" var on header define');
        }

        $pairs  = [];
        $values = Str::explode($vars['copy'], ',');
        unset($vars['copy']);

        // collect other vars
        foreach ($vars as $var => $val) {
            $tplKey = '{$' . $var . '}';

            $pairs[$tplKey] = $val;
        }

        // repeat by values
        foreach ($values as $val) {
            $pairs['{$copy}'] = $val;

            $snippets[] = strtr($template, $pairs);
        }

        $output->success('Complete');
        $output->writeRaw(implode("\n\n", $snippets));
    }
}
