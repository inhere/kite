<?php declare(strict_types=1);

namespace Inhere\KiteTest\Helper;

use Inhere\Kite\Console\Component\Clipboard;
use PHPUnit\Framework\TestCase;
use Toolkit\Stdlib\OS;

/**
 * Class AppHelperTest
 *
 * @package Inhere\KiteTest\Helper
 */
class AppHelperTest extends TestCase
{
    public function testClipboard(): void
    {
        $clip = Clipboard::new();
        self::assertNotEmpty($clip);

        // in github action
        if (OS::getEnvVal('GITHUB_ACTION')) {
            return;
        }

        $current = __METHOD__;
        // vdump($current);
        $clip->write($current, true);

        $text = $clip->read();
        // vdump($text);

        self::assertNotEmpty($text);
        self::assertSame($current, $text);
    }
}
