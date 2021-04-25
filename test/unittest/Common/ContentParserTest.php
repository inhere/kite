<?php declare(strict_types=1);

namespace Inhere\KiteTest\Common;

use Inhere\Kite\Common\IdeaHttp\RequestSet;
use Inhere\KiteTest\BaseTestCase;

/**
 * Class ContentParserTest
 *
 * @package Inhere\KiteTest\Common
 */
class ContentParserTest extends BaseTestCase
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

    public function testParseOne(): void
    {
        $p = RequestSet::new();

        $req = $p->parseOne(self::oneRequest);
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
