<?php

use Inhere\Console\IO\Input;
use Inhere\Kite\Console\CliApplication;
use Inhere\Kite\Console\Plugin\AbstractPlugin;

use League\CommonMark\Environment;
use League\CommonMark\MarkdownConverter;

/**
 * Class DemoPlugin
 */
class DemoPlugin extends AbstractPlugin
{
    public function metadata(): array
    {
        return [
            'desc' => 'this is am demo plugin',
        ];
    }

    /**
     * @param CliApplication $app
     * @param Input          $input
     */
    public function exec(CliApplication $app, Input $input): void
    {
        vdump(__METHOD__);

        $env = Environment::createGFMEnvironment();
        $env->addInlineRenderer($inlineClass, $renderer);
        // $env->
        $env->mergeConfig([]);

        $converter = new MarkdownConverter($env);

        echo $converter->convertToHtml("# Hello GFM!\n welcome");
    }
}
