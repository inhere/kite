<?php declare(strict_types=1);

namespace unittest\Helper;

use Inhere\Kite\Common\Clipboard;
use PHPUnit\Framework\TestCase;
use function vdump;

/**
 * Class AppHelperTest
 *
 * @package unittest\Helper
 */
class AppHelperTest extends TestCase
{
    public function testClipboard(): void
    {
        $clip = Clipboard::new();
        self::assertNotEmpty($clip);

        $current = __METHOD__;
        vdump($current);
        $clip->write($current);

        $text = $clip->read();
        vdump($text);

        self::assertNotEmpty($text);
        self::assertSame($current, $text);
    }
}
