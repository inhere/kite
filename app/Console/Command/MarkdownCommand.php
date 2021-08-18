<?php declare(strict_types=1);
/**
 * This file is part of Kite.
 *
 * @link     https://github.com/inhere
 * @author   https://github.com/inhere
 * @license  MIT
 */

namespace Inhere\Kite\Console\Command;

use Inhere\Console\Command;
use Inhere\Console\IO\Input;
use Inhere\Console\IO\Output;
use Inhere\Kite\Component\CliMarkdown;
use Toolkit\Cli\Color;
use function file_get_contents;

/**
 * Class MarkdownCommand
 */
class MarkdownCommand extends Command
{
    /** @var string  */
    protected static $name = 'markdown';

    /**
     * @var string
     */
    protected static $description = 'render markdown file on terminal';

    /**
     * @return string[]
     */
    public static function aliases(): array
    {
        return ['md', 'mkdown'];
    }

    /**
     * @arguments
     *   mdfile     <red>*</red>The markdown file path.
     *
     * @param  Input $input
     * @param  Output $output
     */
    protected function execute($input, $output)
    {
        $input->bindArgument('mdfile', 0);
        $filename = $input->getRequiredArg('mdfile');

        $text = file_get_contents($filename);

        // parse content
        $md  = new CliMarkdown();
        $doc = $md->parse($text);
        $doc = Color::parseTag(rtrim($doc));

        // $output->colored("Document for the #$nameString");
        $output->writeRaw($doc);
    }
}
