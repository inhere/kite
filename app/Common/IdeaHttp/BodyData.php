<?php declare(strict_types=1);

namespace Inhere\Kite\Common\IdeaHttp;

use Inhere\Kite\Common\MapObject;
use Inhere\Kite\Http\ContentType;
use RuntimeException;
use Toolkit\Stdlib\Str\UrlHelper;
use Toolkit\Stdlib\Type;
use function count;
use function gettype;
use function implode;
use function strtolower;

/**
 * Class FormBody
 *
 * @package Inhere\Kite\Common\IdeaHttp
 */
class BodyData extends MapObject
{
    /**
     * @var string
     */
    protected $contentType = '';

    /**
     * @param string $key
     *
     * @return string
     */
    public function getType(string $key): string
    {
        if (!$this->offsetExists($key)) {
            // return Type::UNKNOWN;
            return Type::UNKNOWN;
        }

        $val = $this->offsetGet($key);
        return Type::get($val);
    }

    /**
     * @param mixed $val
     *
     * @return string
     */
    public function getValType($val): string
    {
        return Type::get($val);
    }

    /**
     * @param array $data
     */
    public function override(array $data): void
    {
        $this->exchangeArray($data);
    }

    /**
     * @param array $data
     * @param bool  $override
     */
    public function load(array $data, bool $override = false): void
    {
        if ($override) {
            $this->override($data);
            return;
        }

        foreach ($data as $key => $val) {
            $this->offsetSet($key, $val);
        }
    }

    /**
     * @param int $limit
     *
     * @return array
     */
    public function getFirstFewData(int $limit = 3): array
    {
        if ($this->isEmpty()) {
            return [];
        }

        $fewData = [];
        foreach ($this as $key => $val) {
            $fewData[$key] = [
                'type'    => Type::get($val, true),
                'example' => $val,
            ];
            if (count($fewData) === $limit) {
                break;
            }
        }

        return $fewData;
    }

    /**
     * @param int $skip
     *
     * @return array
     */
    public function getRemainingData(int $skip = 3): array
    {
        if ($this->isEmpty()) {
            return [];
        }

        $indexNum = 0;
        $othData = [];
        foreach ($this as $key => $val) {
            $indexNum++;
            if ($indexNum <= $skip) {
                continue;
            }

            $othData[$key] = [
                'type'    => Type::get($val, true),
                'example' => $val,
            ];
        }

        return $othData;
    }

    /**
     * @param int $limitParam
     *
     * @return string
     */
    public function genMethodParams(int $limitParam = 3): string
    {
        if ($this->isEmpty()) {
            return 'array $params = []';
        }

        $params = [];
        foreach ($this as $key => $val) {
            $typeName  = Type::get($val, true);
            $params[] = "$typeName \${$key}";
            if (count($params) === $limitParam) {
                break;
            }
        }

        $params[] = 'array $params = []';
        return implode(', ', $params);
    }

    /**
     * @return bool
     */
    public function isEmpty(): bool
    {
        return $this->count() === 0;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->toString();
    }

    /**
     * @param string $cType
     *
     * @return string
     */
    public function getStringByContentType(string $cType): string
    {
        // if ($cType === '') {
        //  return '';
        // }

        if ($cType === ContentType::JSON) {
            return json_encode($this->getArrayCopy());
        }

        if ($cType === ContentType::FORM) {
            return UrlHelper::build("", $this->getArrayCopy());
        }

        throw new RuntimeException("content type '{$cType}' is not supported");
    }

    /**
     * @return string
     */
    public function toString(): string
    {
        return $this->getStringByContentType($this->contentType);
    }

    /**
     * @return string
     */
    public function getContentType(): string
    {
        return $this->contentType;
    }

    /**
     * @param string $cType
     *
     * @return $this
     */
    public function withContentType(string $cType): self
    {
        $this->setContentType($cType);
        return $this;
    }

    /**
     * @param string $contentType
     */
    public function setContentType(string $contentType): void
    {
        if ($contentType) {
            $this->contentType = strtolower($contentType);
        }
    }
}
