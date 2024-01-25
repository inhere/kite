<?php declare(strict_types=1);

namespace Inhere\Kite\Console\Controller\Tool;

use Exception;
use Inhere\Console\Controller;
use Inhere\Console\Exception\PromptException;
use Inhere\Console\IO\Output;
use Inhere\Kite\Console\SubCmd\ToolCmd\HashCommand;
use Inhere\Kite\Console\SubCmd\ToolCmd\RandomCommand;
use Inhere\Kite\Helper\AppHelper;
use Inhere\Kite\Kite;
use PhpPkg\EasyTpl\EasyTemplate;
use PhpPkg\Ini\Ini;
use Toolkit\PFlag\FlagsParser;
use Toolkit\Stdlib\Str;
use Toolkit\Stdlib\Util\UUID;
use Toolkit\Sys\Proc\ProcWrapper;
use function date;
use function explode;
use function file_get_contents;
use function implode;
use function parse_ini_string;
use function strtr;
use function trim;

/**
 * Class GenerateGroup
 *
 * @package Inhere\Kite\Console\Group
 */
class GenerateController extends Controller
{
    protected static string $name = 'gen';

    protected static string $desc = 'quick generate new class or file from template';

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

    protected function subCommands(): array
    {
        return [
            HashCommand::class,
            RandomCommand::class,
        ];
    }

    /**
     * @param Output $output
     */
    public function readmeCommand(Output $output): void
    {
        $output->success('Complete');
    }

    /**
     * Generate a uuid string
     *
     * @options
     *   --version, -v      int; the version of uuid
     *   --raw              bool; output as raw format
     *
     * @param FlagsParser $fs
     * @param Output      $output
     */
    public function uuidCommand(FlagsParser $fs, Output $output): void
    {
        $version = $fs->getOpt('version', 4);

        $u = UUID::new($version);

        if ($fs->getOpt('raw')) {
            $output->println($u->getRaw());
        } else {
            $output->println($u->getValue());
        }
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
     * @param FlagsParser $fs
     * @param Output      $output
     */
    public function templateCommand(FlagsParser $fs, Output $output): void
    {
        $editor   = $fs->getOpt('editor', 'vim');
        $filepath = 'tmp/' . date('Ymd_Hi') . '.tpl';

        ProcWrapper::runEditor($editor, $filepath);

        $output->writeln($filepath);
    }

    /**
     * parse and generate some codes by input template file
     *
     * @arguments
     *  tpl         string;The template filepath;required
     *
     * @param FlagsParser $fs
     * @param Output      $output
     */
    public function parseCommand(FlagsParser $fs, Output $output): void
    {
        $tplFile = $fs->getArg('tpl');
        $tplFile = Kite::alias($tplFile);
        $content = file_get_contents($tplFile);

        [$varDefine, $template] = explode('###', $content);

        // $vars = (array)parse_ini_string(trim($varDefine), true);
        $vars = Ini::decode(trim($varDefine));
        $output->aList($vars, 'template vars', ['ucFirst' => false]);

        $logic  = EasyTemplate::textTemplate();
        $result = $logic->renderString(trim($template), $vars);

        $output->success('Render Result:');
        $output->writeRaw($result);
    }

    /**
     * Repeat generate some codes by input vars
     *
     * @arguments
     *  tpl         string;The template filepath;required
     *
     * @param FlagsParser $fs
     * @param Output      $output
     */
    public function repeatCommand(FlagsParser $fs, Output $output): void
    {
        $tplFile = $fs->getArg('tpl');
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
     *  -t, --type      The type. allow: number, string
     *
     * @param FlagsParser $fs
     * @param Output      $output
     *
     * @throws Exception
     */
    public function idCommand(FlagsParser $fs, Output $output): void
    {
        $type = $fs->getOpt('type', 'number');

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
     *  -l, --length        int;The string length. default: 12
     *  -n, --number        int;The number of generated strings. default: 1
     *  -t, --template      The sample template name. allow: alpha, alpha_num, alpha_num_c
     *
     * @param FlagsParser $fs
     * @param Output      $output
     *
     * @throws Exception
     */
    public function randomCommand(FlagsParser $fs, Output $output): void
    {
        $length = $fs->getOpt('length', 12);
        $number = $fs->getOpt('number', 1);

        if ($number < 1 || $number > 20) {
            $number = 1;
        }

        $sname = $fs->getOpt('template', 'alpha_num');

        if ($number === 1) {
            $str = AppHelper::genRandomStr($sname, $length);
            $output->info('Generated: ' . $str);
            return;
        }

        $list = ['Generated:'];
        for ($i = 0; $i < $number; $i++) {
            $list[] = AppHelper::genRandomStr($sname, $length);
        }

        $output->info($list);
    }

}
