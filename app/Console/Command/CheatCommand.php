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
use Inhere\Kite\Kite;
use InvalidArgumentException;
use PhpComp\Http\Client\AbstractClient;
use PhpComp\Http\Client\Client;
use PhpComp\Http\Client\ClientConst;
use Toolkit\Cli\Cli;
use Toolkit\Cli\Color;
use Toolkit\FsUtil\Dir;
use function dirname;
use function explode;
use function file_exists;
use function file_get_contents;
use function file_put_contents;
use function strpos;
use function substr;
use function trim;

/**
 * Class CheatCommand
 */
class CheatCommand extends Command
{
    public const CHT_HOST = 'https://cht.sh/';

    protected static $name = 'cheat';

    protected static $description = 'Query cheat for development';

    /**
     * @return string[]
     */
    public static function aliases(): array
    {
        return ['cht', 'cht.sh', 'cheat.sh'];
    }

    /**
     * Configure command
     */
    protected function configure(): void
    {
        $fs = $this->getFlags();

        $fs->addArg('topic', 'The language/topic for search. eg: go, php, java, lua, python, js ...');
        $fs->addArg('question', 'The question search.');

        $fs->addOpt('search', 's', 'search by the keywords');
        // $fs->addOpt('T', 't', 'query');

        $fs->setMoreHelp(<<<HELP
<b>Special pages</b>
There are several special pages that are not cheat sheets. Their names start with colon and have special meaning.

Getting started:

    :help               description of all special pages and options
    :intro              cheat.sh introduction, covering the most important usage questions
    :list               list all cheat sheets (can be used in a subsection too: /go/:list)

Command line client cht.sh and shells support:

    :cht.sh             code of the cht.sh client
    :bash_completion    bash function for tab completion
    :bash               bash function and tab completion setup
    :fish               fish function and tab completion setup
    :zsh                zsh function and tab completion setup

Editors support:

    :vim                cheat.sh support for Vim
    :emacs              cheat.sh function for Emacs
    :emacs-ivy          cheat.sh function for Emacs (uses ivy)

Other pages:

    :post               how to post new cheat sheet
    :styles             list of color styles
    :styles-demo        show color styles usage examples
    :random             fetches a random page (can be used in a subsection too: /go/:random)
HELP
        );
        $fs->setExampleHelp([
            '{fullCmd} go reverse list'
        ]);
    }

    /**
     * Query cheat for development
     *
     * github: https://github.com/chubin/cheat.sh
     *
     * curl cheat.sh/tar
     * curl cht.sh/curl
     * curl https://cheat.sh/rsync
     * curl https://cht.sh/php
     *
     * curl cht.sh/go/:list
     * curl cht.sh/go/reverse+a+list
     * curl cht.sh/python/random+list+elements
     * curl cht.sh/js/parse+json
     * curl cht.sh/lua/merge+tables
     * curl cht.sh/clojure/variadic+function
     *
     * @param Input $input
     * @param Output $output
     */
    protected function execute(Input $input, Output $output)
    {
        // search by keywords
        $search = $this->flags->getOpt('search');
        if ($search) {
            $chtApiUrl = self::CHT_HOST . '~' . $search;

            Cli::info('will request remote URL: ' . $chtApiUrl);
            $resp   = $this->httpClient()->get($chtApiUrl);
            $result = trim($resp->getResponseBody());

            $output->colored('RESULT:');
            $output->write($result);
            return;
        }

        $topic = $this->flags->getArg('topic');
        $query = $this->flags->getArg('question');
        if (!$topic) {
            throw new InvalidArgumentException('please input an topic name for query.');
        }

        $result = $this->queryResult($topic, $query);

        $output->colored('RESULT:');
        $output->write($result);
    }

    public const RANDOM_TOPIC = ':random';

    /**
     * @param string $topic
     * @param string $query query question
     * @param bool $refresh
     *
     * @return string
     */
    protected function queryResult(string $topic, string $query, bool $refresh = false): string
    {
        if (!$topic = trim($topic)) {
            throw new InvalidArgumentException('topic cannot be empty');
        }

        $cacheDir = Kite::getPath('tmp/cheat');
        if ($topic[0] === ':') {
            $cacheFile = $cacheDir . "/$topic.txt";
        } else {
            $cacheFile = $cacheDir . '/' . $topic;
            $cacheFile .= $query ? "/$query.txt" : '/_topic.txt';
        }

        if (!$refresh && file_exists($cacheFile)) {
            Cli::info('will read from cache: ' . $cacheFile);
            return file_get_contents($cacheFile);
        }

        $chtApiUrl = self::CHT_HOST . $topic;
        if ($query) {
            $chtApiUrl .= "/$query";
        }

        Cli::info('will request remote URL: ' . $chtApiUrl);
        $resp = $this->httpClient()->get($chtApiUrl);

        // vdump($resp->getResponseBody(), $resp->getResponseHeaders());

        $result  = trim($resp->getResponseBody());
        $headers = $resp->getResponseHeaders();
        $bodyLen = (int)($headers['Content-Length'] ?? 0);

        // not found
        if (
            $bodyLen < 300 &&
            (strpos($result, 'Unknown topic.') !== false || strpos($result, 'Unknown cheat sheet') !== false)
        ) {
            return $result;
        }

        // an random document.
        if ($topic === self::RANDOM_TOPIC) {
            [$firstLine,] = explode("\n", $result);
            // vdump($firstLine);
            $name = trim(Color::clearColor($firstLine), "#/ \t\n\r\0\x0B");
            if (strpos($name, 'cheat:') === 0) {
                $name = substr($name, 6);
            }

            if (!$name) {
                return $result;
            }

            Cli::info('found the random document: ' . $name);
            $cacheFile = $cacheDir . "/random/$name.txt";
        }

        if ($result) {
            Cli::info('write result to cache file: ' . $cacheFile);
            Dir::mkdir(dirname($cacheFile));
            file_put_contents($cacheFile, $result);
        }

        return $result;
    }

    /**
     * @return AbstractClient
     */
    protected function httpClient(): AbstractClient
    {
        return Client::factory([])->setUserAgent(ClientConst::USERAGENT_CURL);
    }
}
