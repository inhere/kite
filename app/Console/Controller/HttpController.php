<?php declare(strict_types=1);
/**
 * This file is part of Kite.
 *
 * @link     https://github.com/inhere
 * @author   https://github.com/inhere
 * @license  MIT
 */

namespace Inhere\Kite\Console\Controller;

use Inhere\Console\Controller;
use Inhere\Console\IO\Output;
use Inhere\Kite\Console\Component\ContentsAutoReader;
use Inhere\Kite\Console\SubCmd\OpenUrlCmd;
use Inhere\Kite\Console\SubCmd\ParseUrlQueryCmd;
use Toolkit\PFlag\FlagsParser;
use Toolkit\Stdlib\Str\UrlHelper;

/**
 * Class HttpController
 */
class HttpController extends Controller
{
    protected static string $name = 'http';

    protected static string $desc = 'Some useful http tool commands';

    /**
     * @return array{string: list<string>}
     */
    protected static function commandAliases(): array
    {
        return [
            'bulk2query'          => ['2query', 'to-query'],
            'dequery'             => ParseUrlQueryCmd::aliases(),
            OpenUrlCmd::getName() => ['open', 'open-url'],
        ];
    }

    protected function subCommands(): array
    {
        return [
            ParseUrlQueryCmd::class,
            OpenUrlCmd::class,
        ];
    }

    /**
     * convert k-v map text to http url query string.
     *
     * @options
     *  -s,--source                 string;The source k-v map text. allow: FILEPATH, @clipboard;true
     *  --not-enc,--not-encode      bool;Not apply url-encode for generated string
     *
     * @param FlagsParser $fs
     * @param Output $output
     */
    public function bulk2queryCommand(FlagsParser $fs, Output $output): void
    {
        $source = $fs->getOpt('source');
        $source = ContentsAutoReader::readFrom($source);

        $output->colored('SOURCE:');
        $output->writeRaw($source);

        $query = str_replace([': ', "\n", ':'], ['=', '&', '='], $source);
        if (!$fs->getOpt('not-encode')) {
            $query = UrlHelper::encode($query);
        }

        $output->colored('RESULT:');
        $output->writeRaw($query);
    }
}
