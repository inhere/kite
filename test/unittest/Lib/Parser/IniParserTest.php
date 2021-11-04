<?php declare(strict_types=1);

namespace Inhere\KiteTest\Lib\Parser;

use Inhere\Kite\Lib\Parser\IniParser;
use Inhere\KiteTest\BaseKiteTestCase;
use function vdump;

class IniParserTest extends BaseKiteTestCase
{
    public function testParseIni_full(): void
    {
        $ini = <<<INI
; comments line
// comments line
# comments line

# simple inline array
inlineArr = [ab, 23, 34.5]

# simple list array
[simpleList]
[] = 567
[] = "some value"

# k-v map
[simpleMap]
[val_one] = 567
[val_two] = 'some value'

# k-v map, equals to the 'simpleMap'
[simpleMap2]
[val_one] = 567
[val_two] = "some value"

# multi level list array
[array]
arr_sub_key[] = "arr_elem_one"
arr_sub_key[] = "arr_elem_two"
arr_sub_key[] = "arr_elem_three"

# multi level k-v map sub array
[array_keys]
val_arr_two[6] = "key_6"
val_arr_two[some_key] = "some_key_value"
INI;
        $data = IniParser::parseString($ini);
        vdump($data);
        $this->assertNotEmpty($data);
        $this->assertArrayHasKey('inlineArr', $data);
        $this->assertEquals(['ab', 23, 34.5], $data['inlineArr']);

        $this->assertArrayHasKey('simpleList', $data);
        $this->assertEquals([567, 'some value'], $data['simpleList']);

        $this->assertArrayHasKey('simpleMap', $data);
        $this->assertEquals(['val_one' => 567, 'val_two' => 'some value'], $data['simpleMap']);
    }

    public function testParseIni_1levelList(): void
    {
        $ini = '
# simple list array
[simpleList]
[] = 567
[] = "some value"
';
        $data = IniParser::parseString($ini);
        vdump($data);
        $this->assertNotEmpty($data);
        $this->assertArrayHasKey('simpleList', $data);
        $this->assertEquals([567, 'some value'], $data['simpleList']);
    }
}
