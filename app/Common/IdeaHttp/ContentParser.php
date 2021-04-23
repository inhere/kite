<?php declare(strict_types=1);

namespace Inhere\Kite\Common\IdeaHttp;

use RuntimeException;
use Toolkit\Stdlib\Str;
use function count;
use function file_get_contents;
use function file_put_contents;
use function implode;
use function trim;

/**
 * Class ContentParser
 *
 * @package Inhere\Kite\Common\IdeaHttp
 */
class ContentParser
{
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

    /**
     * @param string $filepath
     */
    public function parseFile(string $filepath): void
    {
        $fileCode = file_get_contents($filepath);

        $this->parse($fileCode);
    }

    /**
     * @param string $fileCode
     */
    public function parse(string $fileCode): void
    {
        $fileCode = "\n" . trim($fileCode);
        $codeList = Str::explode($fileCode, Request::REQUEST_SPLIT);

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
    public function getRequest(int $index): ?Request
    {
        return $this->requests[$index] ?? null;
    }

    /**
     * @param string $keywords use keyword find request, will match on title and url
     *
     * @return Request|null
     */
    public function findRequest(string $keywords): ?Request
    {
        $requests = $this->search($keywords, 1);

        return $requests[0] ?? null;
    }

    /**
     * @param string $keywords use keyword find request, will match on title and url
     * @param int    $limit
     *
     * @return Request[]
     */
    public function search(string $keywords, int $limit = 5): array
    {
        $matched = [];
        foreach ($this->requests as $request) {
            if ($request->match($keywords)) {
                $matched[] = $request;

                if (count($matched) >= $limit) {
                    break;
                }
            }
        }

        return $matched;
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

        return implode("\n", $strings);
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
}
