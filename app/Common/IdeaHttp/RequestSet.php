<?php declare(strict_types=1);

namespace Inhere\Kite\Common\IdeaHttp;

use Countable;
use Generator;
use RuntimeException;
use Toolkit\Stdlib\Str;
use function count;
use function file_get_contents;
use function file_put_contents;
use function implode;
use function trim;

/**
 * Class RequestSet
 *
 * @package Inhere\Kite\Common\IdeaHttp
 */
class RequestSet implements Countable
{
    /**
     * @var string
     */
    private $rawContents = '';

    /**
     * @var Request[]
     */
    private $requests = [];

    /**
     * @return static
     */
    public static function new(): self
    {
        return new self();
    }

    public function setEnvArray(): void
    {

    }

    public function setEnvData(): void
    {

    }

    public function setVars(): void
    {

    }

    /**
     * @param string $filepath
     */
    public function loadFromFile(string $filepath): void
    {
        if (!file_exists($filepath)) {
            throw new RuntimeException('the http-client file not exists. file: ' . $filepath);
        }

        $contents = file_get_contents($filepath);
        $this->loadFromString($contents);
    }

    /**
     * @param string $fileCode
     */
    public function loadFromString(string $fileCode): void
    {
        $fileCode = "\n" . trim($fileCode);
        $codeList = Str::explode($fileCode, Request::REQUEST_SPLIT);

        $this->rawContents = $fileCode;
        foreach ($codeList as $code) {
            $this->requests[] = Request::fromHTTPString(Request::START_PREFIX . $code);
        }
    }

    /**
     * @param string $codeString
     *
     * @return Request
     */
    public function parseOne(string $codeString): Request
    {
        return Request::fromHTTPString($codeString);
    }

    /**
     * @param int $index
     *
     * @return Request|null
     */
    public function getByIndex(int $index): ?Request
    {
        return $this->requests[$index] ?? null;
    }

    /**
     * @param string $path
     *
     * @return Request|null
     */
    public function getByPath(string $path): ?Request
    {
        $request = null;
        foreach ($this->requests as $item) {
            if ($item->pathIsEqual($path)) {
                $request = $item;
                break;
            }
        }

        return $request;
    }

    /**
     * @param string $keywords use keyword find request, will match on title and url
     *
     * @return Request|null
     */
    public function findOne(string $keywords): ?Request
    {
        $requests = $this->search($keywords, 1);

        return $requests[0] ?? null;
    }

    /**
     * @param string $keywords use keyword find request, will match on title and url
     * @param int    $limit
     * @param array  $opts
     *                    - matchType allow: matchBoth, matchUri, matchTitle
     *
     * @return Request[]
     */
    public function search(string $keywords, int $limit = 5, array $opts = []): array
    {
        $matched = [];
        $matchTpy = $opts['matchType'] ?? Request::MATCH_BOTH;

        foreach ($this->requests as $request) {
            if ($request->match($keywords, $matchTpy)) {
                $matched[] = $request;

                if (count($matched) >= $limit) {
                    break;
                }
            }
        }

        return $matched;
    }

    /**
     * @param string $keywords
     * @param array  $opts
     *
     * @return Request[]|Generator|null
     */
    public function yieldSearch(string $keywords, array $opts = []): ?Generator
    {
        $matchTpy = $opts['matchType'] ?? Request::MATCH_BOTH;
        foreach ($this->requests as $request) {
            if ($request->match($keywords, $matchTpy)) {
                yield $request;
            }
        }
        return null;
    }

    /**
     * @return Request[]|Generator|null
     */
    public function yieldEach(): ?Generator
    {
        foreach ($this->requests as $request) {
            yield $request;
        }
        return null;
    }

    /**
     * @param Request $request
     */
    public function addRequest(Request $request): void
    {
        $this->requests[] = $request;
    }

    /**
     * @param string $filepath
     */
    public function dumpFile(string $filepath): void
    {
        $ok = file_put_contents($filepath, $this->toString());

        if (!$ok) {
            throw new RuntimeException('dump requests to file error');
        }
    }

    /**
     * @return string
     */
    public function toString(): string
    {
        $strings = [];
        foreach ($this->requests as $request) {
            $strings[] = $request->toHTTPString();
        }

        return implode("\n\n", $strings);
    }

    /**
     * @return Request[]
     */
    public function getRequests(): array
    {
        return $this->requests;
    }

    /**
     * @param Request[] $requests
     */
    public function setRequests(array $requests): void
    {
        $this->requests = $requests;
    }

    /**
     * @return string
     */
    public function getRawContents(): string
    {
        return $this->rawContents;
    }

    /**
     * Count elements of an object
     *
     * @link https://php.net/manual/en/countable.count.php
     * @return int The custom count as an integer.
     */
    public function count(): int
    {
        return count($this->requests);
    }
}
