<?php declare(strict_types=1);
/**
 * This file is part of PTool.
 *
 * @link     https://github.com/inhere
 * @author   https://github.com/inhere
 * @license  MIT
 */

namespace Inhere\PTool\Console\Command;

use Inhere\Console\Command;
use Inhere\Console\IO\Input;
use Inhere\Console\IO\Output;
use Inhere\PTool\Common\CliMarkdown;
use Toolkit\Cli\Color;

/**
 * Class MarkdownCommand
 */
class MarkdownCommand extends Command
{
    protected static $name = 'md:render';

    protected static $description = 'render markdown file on terminal';

    /**
     * do execute
     * @param  Input $input
     * @param  Output $output
     */
    protected function execute($input, $output)
    {
        $filename = $input->getFirstArg();

        $text = \file_get_contents($filename);

        // parse content
        $md  = new CliMarkdown();
        $doc = $md->parse($text);
        $doc = Color::parseTag(rtrim($doc));

        // $output->colored("Document for the #$nameString");
        $output->writeRaw($doc);
    }
}
