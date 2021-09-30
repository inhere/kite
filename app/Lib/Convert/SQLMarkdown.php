<?php declare(strict_types=1);

namespace Inhere\Kite\Lib\Convert;

use Swoft\Swlib\HttpClient;
use function explode;
use function sprintf;

/**
 * Class SQLMarkdown
 */
class SQLMarkdown
{
    private $source;

    private $result;

    /**
     * @param string $createSql Table create SQL.
     *
     * @return string
     */
    public function toMarkdown(string $createSql =''): string
    {
        # code...
    }

    public function toCreateSQL(string $markdown): void
    {
        # code...
    }
}