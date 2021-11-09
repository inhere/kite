<?php declare(strict_types=1);

namespace Inhere\Kite\Console\Command;

use Inhere\Console\Command;
use Inhere\Console\IO\Input;
use Inhere\Console\IO\Output;
use function strpos;
use function substr;

/**
 * class SearchCommand
 */
class SearchCommand extends Command
{
    protected static $name = 'search';

    protected static $desc = 'Search by web search engine, such as google, baidu, bing';

    private array $engines = [
        'google'        => 'https://www.google.com/search?q=',
        'bing'          => 'https://www.bing.com/search?q=',
        'yahoo'         => 'https://search.yahoo.com/search?p=',
        'duckduckgo'    => 'https://www.duckduckgo.com/?q=',
        'startpage'     => 'https://www.startpage.com/do/search?q=',
        'yandex'        => 'https://yandex.ru/yandsearch?text=',
        'github'        => 'https://github.com/search?q=',
        'baidu'         => 'https://www.baidu.com/s?wd=',
        'ecosia'        => 'https://www.ecosia.org/search?q=',
        'goodreads'     => 'https://www.goodreads.com/search?q=',
        'qwant'         => 'https://www.qwant.com/?q=',
        'givero'        => 'https://www.givero.com/search?q=',
        'stackoverflow' => 'https://stackoverflow.com/search?q=',
        'wolframalpha'  => 'https://www.wolframalpha.com/input/?i=',
        'archive'       => 'https://web.archive.org/web/*/',
        'scholar'       => 'https://scholar.google.com/scholar?q=',
    ];

    /**
     * Search by web search engine, such as google, baidu, bing
     *
     * @arguments
     * keywords     The keywords for search
     *
     * @options
     *  -e, --engine    string;The web search engine name;;google
     *  -l, --list      bool;list all web search engine
     *
     * @param Input $input
     * @param Output $output
     *
     * @return int
     */
    protected function execute(Input $input, Output $output): int
    {
        if ($this->flags->getOpt('list')) {
            $output->aList($this->getEngineList(), 'Engine List');
            return 0;
        }

        // on MAC: open URL
        // on MAC: open_command URL
        return 0;
    }

    private function getEngineList(): array
    {
        $list = [];
        foreach ($this->engines as $name => $url) {
            if ($pos = strpos($url, '.com/')) {
                $url = substr($url, 0, $pos+4);
            } elseif ($pos = strpos($url, '.org/')) {
                $url = substr($url, 0, $pos+4);
            } elseif ($pos = strpos($url, '.ru/')) {
                $url = substr($url, 0, $pos+4);
            }

            $list[$name] = $url;
        }

        return $list;
    }
}
