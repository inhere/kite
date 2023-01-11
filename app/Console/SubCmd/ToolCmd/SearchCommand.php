<?php declare(strict_types=1);

namespace Inhere\Kite\Console\SubCmd\ToolCmd;

use Inhere\Console\Command;
use Inhere\Console\IO\Input;
use Inhere\Console\IO\Output;
use Inhere\Kite\Helper\AppHelper;
use InvalidArgumentException;
use Toolkit\Stdlib\Str\UrlHelper;
use function implode;
use function strpos;
use function substr;

/**
 * class SearchCommand
 */
class SearchCommand extends Command
{
    protected static string $name = 'search';

    protected static string $desc = 'Search by web search engine, such as google, baidu, bing';

    private array $engines = [
        'google'        => 'https://www.google.com/search?q=',
        'bing'          => 'https://www.bing.com/search?q=',
        'you'       => 'https://you.com/search?q=',
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
     * @var array{string, string}
     */
    private array $engAliases = [
        'bi'  => 'bing',
        'b'   => 'baidu',
        'bd'  => 'baidu',
        'g'   => 'google',
        'gg'  => 'google',
        'gh'  => 'github',
        'ddg' => 'duckduckgo',
        'sf'  => 'stackoverflow',
    ];

    public static function aliases(): array
    {
        return ['s'];
    }

    /**
     * Search by web search engine, such as google, baidu, bing
     *
     * @arguments
     * keywords     array;The keywords for search;true
     *
     * @options
     *  -e, --engine    string;The web search engine name;;baidu
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
            $output->aList($this->engAliases, 'Engine Aliases');
            $output->aList($this->getEngineList(), 'Engine List');
            return 0;
        }

        $engine = $this->flags->getOpt('engine');
        $engine = $this->engAliases[$engine] ?? $engine;
        if (!isset($this->engines[$engine])) {
            throw new InvalidArgumentException('invalid engine name: ' . $engine);
        }

        $engUrl   = $this->engines[$engine];
        $keywords = $this->flags->getArg('keywords');
        $queryStr = UrlHelper::encode(implode(' ', $keywords));

        // on MAC: open URL
        // on MAC: open_command URL
        AppHelper::openBrowser($engUrl . $queryStr);
        return 0;
    }

    private function getEngineList(): array
    {
        $list = [];
        foreach ($this->engines as $name => $url) {
            if ($pos = strpos($url, '.com/')) {
                $url = substr($url, 0, $pos + 4);
            } elseif ($pos = strpos($url, '.org/')) {
                $url = substr($url, 0, $pos + 4);
            } elseif ($pos = strpos($url, '.ru/')) {
                $url = substr($url, 0, $pos + 4);
            }

            $list[$name] = $url;
        }

        return $list;
    }
}
