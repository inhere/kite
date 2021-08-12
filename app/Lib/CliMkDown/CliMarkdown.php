<?php declare(strict_types=1);

namespace Inhere\Kite\Lib\CliMkDown;

use League\CommonMark\Environment;
use League\CommonMark\MarkdownConverter;

/**
 * Class CliMarkdown
 *
 * @package Inhere\Kite\Lib\CliMkDown
 */
class CliMarkdown
{
    /**
     * The document content language
     *
     * @var string
     */
    private $lang = '';

    /**
     * @return string
     */
    public function render(): string
    {
        $env = Environment::createGFMEnvironment();
        // $env->addInlineRenderer($inlineClass, $renderer);
        // $env->
        $env->mergeConfig([]);

        $converter = new MarkdownConverter($env);

        echo $converter->convertToHtml("# Hello GFM!\n welcome");
    }
}
