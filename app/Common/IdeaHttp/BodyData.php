<?php declare(strict_types=1);

namespace Inhere\Kite\Common\IdeaHttp;

use Inhere\Kite\Http\ContentType;
use RuntimeException;
use Toolkit\Stdlib\Str\UrlHelper;
use function strtolower;

/**
 * Class FormBody
 *
 * @package Inhere\Kite\Common\IdeaHttp
 */
class BodyData extends AbstractBody
{
    /**
     * @var string
     */
    protected $contentType = ContentType::FORM;

    /**
     * @param string $cType
     *
     * @return string
     */
    public function getStringByContentType(string $cType): string
    {
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
    public function __toString(): string
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
     * @param string $contentType
     */
    public function setContentType(string $contentType): void
    {
        if ($contentType) {
            $this->contentType = strtolower($contentType);
        }
    }
}
