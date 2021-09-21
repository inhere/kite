<?php declare(strict_types=1);

namespace Inhere\Kite\Common\IdeaHttp;

use Inhere\Kite\Http\ContentType;
use Inhere\Route\Router;
use InvalidArgumentException;
use RuntimeException;
use Toolkit\Stdlib\Str;
use function array_merge;
use function count;
use function explode;
use function implode;
use function in_array;
use function is_array;
use function json_decode;
use function method_exists;
use function parse_str;
use function parse_url;
use function strpos;
use function strtolower;
use function strtoupper;
use function trim;
use function ucfirst;
use const PHP_EOL;

/**
 * Class Request
 *
 * @package Inhere\Kite\Common\IdeaHttp
 */
class Request
{
    public const REQUEST_SPLIT = "\n###";

    public const START_PREFIX = '###';

    public const BODY_SPLIT = "\n\n";

    public const CONTENT_TYPE = 'Content-Type';

    public const NO_BODY = ['GET', 'HEAD', 'DELETE'];

    // public const NO_INIT = '-';

    // matchType allow: both, uri, path, title
    public const MATCH_BOTH  = 'both';
    public const MATCH_URL   = 'url';
    public const MATCH_PATH  = 'path';
    public const MATCH_TITLE = 'title';

    /**
     * @var string
     */
    private $title = '';

    /**
     * @var string
     */
    private $method = 'GET';

    /**
     * @var string
     */
    private $url = '';

    /**
     * @var UrlInfo
     * @see \parse_url()
     */
    private $urlInfo;

    /**
     * @var string
     */
    private $headerRaw = '';

    /**
     * @var array
     */
    private $headers = [];

    /**
     * @var string
     */
    private $bodyRaw = '';

    /**
     * @var BodyData
     */
    private $bodyData;

    /**
     * @param array $data
     *
     * @return static
     */
    public static function new(array $data = []): self
    {
        return new self($data);
    }

    /**
     * @param string $str
     *
     * @return static|null
     */
    public static function fromHTTPString(string $str): ?self
    {
        $str = trim($str);
        if (!$str) {
            throw new RuntimeException('empty http code string for parse');
        }

        // parse meta
        $title = '';

        // - parse title
        if (strpos($str, '###') === 0) {
            $nodes = explode("\n", $str, 2);
            $title = trim($nodes[0], " \t\n\r\0\x0B#");
            $str   = $nodes[1] ? trim($nodes[1]) : '';
        }

        if (!$str) {
            throw new RuntimeException('invalid request string, not found url line');
        }

        // \vdump($str);
        // split meta and body
        $mbNodes = Str::explode($str, self::BODY_SPLIT, 2);
        // assign
        $meta = $mbNodes[0];
        $body = $mbNodes[1] ?? '';
        // \vdump(\parse_str($body));
        // \vdump($meta);

        // split murl and headers
        // $nodes = explode("\n", $meta, 2);
        // - will filter comments line
        if (!$nodes = self::explodeMeta($meta)) {
            return null;
        }

        $murl = trim($nodes[0]);

        // - parse method and url
        $muNodes = Str::explode($murl, ' ', 2);
        if (count($muNodes) !== 2) {
            throw new RuntimeException("invalid request string, error url line: '{$murl}'");
        }

        [$method, $url] = $muNodes;
        // \vdump(parse_url($url));

        $method = strtoupper($method);
        if (!in_array($method, Router::METHODS_ARRAY, true)) {
            throw new InvalidArgumentException("the request method:{$method} is invalid or not suppprted");
        }

        $head = $nodes[1] ?? '';
        $data = [
            'title'     => $title,
            'method'    => $method,
            'url'       => $url,
            'headerRaw' => $head,
            'bodyRaw'   => $body,
        ];

        return new self($data);
    }

    /**
     * will filter comments line
     *
     * @param string $meta
     *
     * @return array
     */
    private static function explodeMeta(string $meta): array
    {
        $murl = '';
        $headers = [];
        foreach (explode("\n", $meta) as $line) {
            // is comments line
            if (strpos($line, '#') === 0) {
                continue;
            }

            if ($murl) {
                $headers[] = $line;
            } else {
                $murl = $line;
            }
        }

        if ($murl) {
            return [$murl, implode("\n", $headers)];
        }
        return [];
    }

    /**
     * Class constructor.
     *
     * @param array $data
     */
    public function __construct(array $data = [])
    {
        if ($data) {
            $this->loadData($data);
        }
    }

    /**
     * @param array $data
     */
    public function loadData(array $data): void
    {
        foreach ($data as $key => $val) {
            $setter = 'set' . ucfirst($key);
            if (method_exists($this, $setter)) {
                if ($key === 'method') {
                    $val = strtoupper($val);
                }

                $this->$setter($val);
            }
        }
    }

    /**
     * @return string
     */
    public function getContentType(): string
    {
        $headers = $this->getHeaders();
        $cTypes  = $headers['content-type'] ?? [];

        return $cTypes ? strtolower($cTypes[0]) : '';
    }

    /**
     * @param string $keywords use keyword find request, will match on title and url
     * @param string $matchTpye
     *
     * @return bool
     */
    public function match(string $keywords, string $matchTpye = self::MATCH_BOTH): bool
    {
        if ($this->title && strpos($this->title, $keywords) !== false) {
            return true;
        }

        if ($this->url && strpos($this->url, $keywords) !== false) {
            return true;
        }

        return false;
    }

    /**
     * @param string $path
     *
     * @return bool
     */
    public function pathIsEqual(string $path): bool
    {
        return $this->getUrlInfo()->getString('path') === $path;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'title'     => $this->title,
            'method'    => $this->method,
            'url'       => $this->url,
            'headerRaw' => $this->headerRaw,
            'bodyRaw'   => $this->bodyRaw,
        ];
    }

    /**
     * Convert to CURL code string
     *
     * @return string
     */
    public function toCURLString(): string
    {
        return 'TODO';
    }

    /**
     * Convert to HTTP request code string
     *
     * @return string
     */
    public function toHTTPString(): string
    {
        $str = '';
        if ($this->title) {
            $str = "### {$this->title}\n";
        }

        $str .= "{$this->method} {$this->url}\n";

        // append header data
        if ($headerRaw = $this->getHeaderRaw()) {
            $str .= $headerRaw;
        }

        // append body data
        if (!in_array($this->method, self::NO_BODY, true)) {
            $bodyString = $this->getBodyRaw();
            if ($bodyString) {
                $str .= self::BODY_SPLIT . $bodyString;
            }
        }

        return $str;
    }

    public function __toString(): string
    {
        return $this->toHTTPString();
    }

    /**
     * @return UrlInfo
     */
    public function getUrlInfo(): UrlInfo
    {
        if ($this->url && !$this->urlInfo) {
            $urlInfoData = parse_url($this->url);
            if (!$urlInfoData) {
                throw new InvalidArgumentException('invalid http url string: ' . $this->url);
            }

            $urlInfoData['url'] = $this->url;

            // set
            $this->urlInfo = UrlInfo::new($urlInfoData);
        }

        return $this->urlInfo;
    }

    /**
     * @return string
     */
    public function getHeaderRaw(): string
    {
        if (!$this->headerRaw && $this->headers) {
            foreach ($this->headers as $name => $value) {
                $valueString = is_array($value) ? implode('; ', $value) : (string)$value;

                // build inline
                $this->headerRaw .= "{$name}:{$valueString}\n";
            }
        }

        return $this->headerRaw;
    }

    /**
     * @param string $headerRaw
     */
    public function setHeaderRaw(string $headerRaw): void
    {
        $this->headerRaw = trim($headerRaw);
    }

    /**
     * @return array
     */
    public function getHeaders(): array
    {
        if (!$this->headers && $this->headerRaw) {
            $this->headers = $this->parseHeaderRaw($this->headerRaw);
        }

        return $this->headers;
    }

    /**
     * @param string $headerRaw
     *
     * @return array
     */
    public function parseHeaderRaw(string $headerRaw): array
    {
        $headerMap = [];
        $headerRaw = trim($headerRaw);

        foreach (explode("\n", $headerRaw) as $line) {
            $nodes = Str::explode($line, ':', 2);
            $name  = strtolower($nodes[0]);
            $value = $nodes[1] ?? '';
            if ($value && strpos($value, ';') !== false) {
                $headerMap[$name] = Str::explode($value, ';');
            } else {
                $headerMap[$name] = [$value];
            }
        }

        return $headerMap;
    }

    /**
     * @param array $headers
     * @param bool  $mergeOld
     */
    public function setHeaders(array $headers, bool $mergeOld = true): void
    {
        $this->headers = $mergeOld ? array_merge($this->headers, $headers) : $headers;
        // reset raw string.
        $this->headerRaw = '';
    }

    /**
     * @return string
     */
    public function getBodyRaw(): string
    {
        if ($this->bodyData) {
            return $this->bodyData->toString();
        }

        return $this->bodyRaw;
    }

    /**
     * @param string $bodyRaw
     */
    public function setBodyRaw(string $bodyRaw): void
    {
        $this->bodyRaw = trim($bodyRaw);
    }

    /**
     * @return BodyData
     */
    public function getBodyData(): BodyData
    {
        if (!$this->bodyData && $this->bodyRaw) {
            $this->bodyData = $this->parseBodyRaw($this->bodyRaw);
        } elseif (!$this->bodyData) {
            $this->bodyData = BodyData::new();
        }

        return $this->bodyData;
    }

    /**
     * @param string $bodyRaw
     *
     * @return BodyData
     */
    protected function parseBodyRaw(string $bodyRaw): BodyData
    {
        $cType = $this->getContentType();
        if (!$cType) {
            throw new RuntimeException('content type is not found, cannot parse body data');
        }

        if ($cType === ContentType::JSON) {
            $arr = json_decode($bodyRaw, true);
            return BodyData::new($arr)->withContentType($cType);
        }

        if ($cType === ContentType::FORM) {
            $result = [];
            parse_str($bodyRaw, $result);
            return BodyData::new($result)->withContentType($cType);
        }

        throw new RuntimeException("content type '{$cType}' is not supported for parse body");
    }

    /**
     * @param array $bodyData
     * @param bool  $override
     */
    public function setBodyData(array $bodyData, bool $override = false): void
    {
        if ($this->bodyData) {
            $this->bodyData->load($bodyData, $override);
        } else {
            $this->bodyData = JsonBody::new($bodyData);
        }

        // reset raw string.
        $this->bodyRaw = '';
    }

    /**
     * @return string
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * @param string $method
     */
    public function setMethod(string $method): void
    {
        if ($method) {
            $this->method = strtoupper($method);
        }
    }

    /**
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * @param string $url
     */
    public function setUrl(string $url): void
    {
        if ($url) {
            $this->url = trim($url);
        }
    }

    /**
     * @param bool $newline
     *
     * @return string
     */
    public function getTitle(bool $newline = false): string
    {
        return $newline ? $this->title . PHP_EOL : $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle(string $title): void
    {
        if ($title) {
            $this->title = trim($title);
        }
    }
}
