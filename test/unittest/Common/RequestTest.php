<?php declare(strict_types=1);

namespace Inhere\KiteTest\Common;

use Inhere\Kite\Common\IdeaHttp\Request;
use Inhere\Kite\Common\IdeaHttp\RequestSet;
use Inhere\KiteTest\BaseKiteTestCase;

/**
 * Class RequestTest
 *
 * @package Inhere\KiteTest\Common
 */
class RequestTest extends BaseKiteTestCase
{
    public const oneRequest = <<<HTTP
### post example
POST http://{{host}}/posts/add
Content-Type: application/json

{
  "title": "post title",
  "content": "post content"
}

HTTP;

    public function testFromHTTPString(): void
    {
        $req = Request::fromHTTPString(self::oneRequest);
        // \vdump($req);

        self::assertNotEmpty($req);
        self::assertNotEmpty($req->getHeaders());
        self::assertSame('application/json', $req->getContentType());
        // \vdump($req->getUrlInfo(), $req->getHeaders());
        self::assertNotEmpty($bd = $req->getBodyData());
        \vdump($bd);
        // self::assertNotEmpty($data = $r->getEnvData('dev'));
        // self::assertArrayHasKey('host', $data);
    }
}
