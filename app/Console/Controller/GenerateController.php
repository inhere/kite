<?php declare(strict_types=1);

namespace Inhere\Kite\Console\Controller;

use Exception;
use Inhere\Console\Controller;
use Inhere\Console\Exception\PromptException;
use Inhere\Console\IO\Input;
use Inhere\Console\IO\Output;
use Inhere\Kite\Common\Template\TextTemplate;
use Toolkit\Stdlib\Str;
use Toolkit\Sys\Proc\ProcWrapper;
use function date;
use function explode;
use function file_get_contents;
use function implode;
use function is_string;
use function parse_ini_string;
use function random_int;
use function strlen;
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
            'rpt'    => 'repeat',
            'tpl'    => 'template',
            'random' => ['rdm', 'rand'],
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


    /**
     * generate an unique ID string.
     *
     * @options
     *  -t, --type  The type. allow: number, string
     *
     * @param Input  $input
     * @param Output $output
     *
     * @throws Exception
     */
    public function idCommand(Input $input, Output $output): void
    {
        $type = $input->getSameStringOpt('t,type', 'number');

        if ($type === 'number') {
            $id = Str::genNOV1();
        } else {
            $id = Str::genNOV2();
        }

        $output->info('Generated: ' . $id);
    }

    /**
     * generate an random string.
     *
     * @options
     *  -l, --length    The string length
     *  -t, --template  The sample template name. allow: alpha, alpha_num, alpha_num_c
     *
     * @param Input  $input
     * @param Output $output
     *
     * @throws Exception
     */
    public function randomCommand(Input $input, Output $output): void
    {
        $length  = $input->getSameIntOpt('l,length', 12);
        $samples = [
            'alpha'        => 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz',
            'alpha_num'    => '0123456789abcdefghijklmnopqrstuvwxyz',
            'alpha_num_up' => '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz',
            'alpha_num_c'  => '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz-+!@#$%&*',
        ];

        $sname = $input->getSameStringOpt('t,template', 'alpha_num');
        $chars = $samples[$sname] ?? $samples['alpha_num'];

        $str = '';
        $max = strlen($chars) - 1;   //strlen($chars) 计算字符串的长度

        for ($i = 0; $i < $length; $i++) {
            $str .= $chars[random_int(0, $max)];
        }

        $output->info('Generated: ' . $str);
    }

}
