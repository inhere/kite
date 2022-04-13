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
 * Class BatchCommand
 */
class BatchCommand extends Command
{
    /** @var string  */
    protected static string $name = 'batch';

    /**
     * @var string
     */
    protected static string $desc = 'batch run an reguer command multi times';

    /**
     * @return string[]
     */
    public static function aliases(): array
    {
        return ['brun', 'batch-run'];
    }

    /**
     * @arguments
     *   mdfile     string;The markdown file path;required
     *
     * @param  Input $input
     * @param  Output $output
     */
    protected function execute(Input $input, Output $output)
    {
        $filename = $this->flags->getArg('mdfile');

        $text = file_get_contents($filename);

        // parse content
        $md  = new CliMarkdown();
        $doc = $md->parse($text);
        $doc = Color::parseTag(rtrim($doc));

        // $output->colored("Document for the #$nameString");
        $output->writeRaw($doc);
    }
}
