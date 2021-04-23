<?php declare(strict_types=1);

namespace Inhere\Kite\Common\IdeaHttp;

use Toolkit\Stdlib\Str;
use function array_merge;
use function implode;
use function in_array;
use function is_array;
use function strpos;
use function strtoupper;
use function trim;

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
     * @var array
     */
    private $bodyData = [];

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
     * @param string $codeString
     *
     * @return static
     */
    public static function fromHTTPString(string $codeString): self
    {
        $codeString = \trim($codeString);

        [$meta, $body] = Str::explode(Request::BODY_SPLIT, $codeString);

        // parse meta
        $meta = \trim($meta);

        $header = $title = '';
        if (strpos($meta, '###') === 0) {
            $nodes = \explode("\n", $meta, 2);
            $title = \trim($nodes[0]);
            $meta  = $nodes[1] ?? '';
        }

        if (!$meta) {
            throw new \RuntimeException('invalid http code string, not found url line');
        }

        // parse body
        $body = \trim($body);

        $data = [
            'title'     => $title,
            'headerRaw' => $header,
            'bodyRaw'   => $body,
        ];

        return Request::new($data);
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
            $setter = 'set' . \ucfirst($key);
            if (\method_exists($this, $setter)) {
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

    }

    /**
     * @param string $keywords use keyword find request, will match on title and url
     *
     * @return bool
     */
    public function match(string $keywords): bool
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

    }

    /**
     * Convert to HTTP request code string
     *
     * @return string
     */
    public function toHTTPString(): string
    {
        $str = <<<HTTP
{$this->method} {$this->url}
HTTP;

        if ($this->title) {
            $str = "### " . $this->title;
        }

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
        $this->headerRaw = $headerRaw;
    }

    /**
     * @return string
     */
    public function getBodyRaw(): string
    {
        if (!$this->bodyRaw && $this->bodyData) {

        }

        return $this->bodyRaw;
    }

    /**
     * @param string $bodyRaw
     */
    public function setBodyRaw(string $bodyRaw): void
    {
        $this->bodyRaw = $bodyRaw;
    }

    /**
     * @return array
     */
    public function getHeaders(): array
    {
        if (!$this->headers && $this->headerRaw) {

        }

        return $this->headers;
    }

    public function parseHeaderRaw(string $headerRaw): array
    {
        $headerMap = [];
        $headerRaw = trim($headerRaw);

        foreach (\explode("\n", $headerRaw) as $line) {
            $nodes = Str::explode($line, ':', 2);
            $name  = \strtolower($nodes[0]);
            $value = $nodes[1] ?? '';
            if ($value && strpos(';', $value) !== false) {

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
     * @return array
     */
    public function getBodyData(): array
    {
        // TODO parse body raw
        return $this->bodyData;
    }

    /**
     * @param array $bodyData
     * @param bool  $mergeOld
     */
    public function setBodyData(array $bodyData, bool $mergeOld = true): void
    {
        $this->bodyData = $mergeOld ? array_merge($this->bodyData, $bodyData) : $bodyData;
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
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
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
