<?php declare(strict_types=1);

namespace Inhere\KiteTest\Common;

use Inhere\Kite\Common\IdeaHttp\ClientEnvReader;
use Inhere\KiteTest\BaseTestCase;

/**
 * Class ClientEnvReaderTest
 *
 * @package Inhere\KiteTest\Common
 */
class ClientEnvReaderTest extends BaseTestCase
{
    public function testLoad(): void
    {
        $efile = \BASE_PATH . '/test/clienttest/http-client.env.json';
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
