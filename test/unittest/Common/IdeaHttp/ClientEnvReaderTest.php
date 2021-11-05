<?php declare(strict_types=1);

namespace Inhere\KiteTest\Common\IdeaHttp;

use Inhere\Kite\Common\IdeaHttp\ClientEnvReader;
use Inhere\KiteTest\BaseKiteTestCase;

/**
 * Class ClientEnvReaderTest
 *
 * @package Inhere\KiteTest\Common\IdeaHttp
 */
class ClientEnvReaderTest extends BaseKiteTestCase
{
    public function testLoad(): void
    {
        $efile = \BASE_PATH . '/test/httptest/http-client.env.json';
        $r = ClientEnvReader::new($efile);

        self::assertTrue($r->load());
        self::assertNotEmpty($arr = $r->getEnvArray('dev'));
        self::assertArrayHasKey('host', $arr);
        self::assertSame('127.0.0.1:10106', $arr['host']);

        self::assertNotEmpty($data = $r->getEnvData('dev'));
        self::assertArrayHasKey('host', $data);
        self::assertSame('127.0.0.1:10106', $data->getValue('host'));
    }
}
