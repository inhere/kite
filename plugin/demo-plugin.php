<?php

use Inhere\Console\IO\Input;
use Inhere\Kite\Console\Application;
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

    public function exec(Application $app, Input $input): void
    {
        vdump(__METHOD__);

        $env = Environment::createGFMEnvironment();
        $env->mergeConfig([]);

        $converter = new MarkdownConverter($env);

        echo $converter->convertToHtml("# Hello GFM!\n welcome");
    }
}
