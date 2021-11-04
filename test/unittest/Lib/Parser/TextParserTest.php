<?php declare(strict_types=1);

namespace Inhere\KiteTest\Lib\Parser;

use Inhere\Kite\Lib\Parser\Text\TextParser;
use Inhere\KiteTest\BaseKiteTestCase;
use function vdump;

/**
 * class TextParserTest
 */
class TextParserTest extends BaseKiteTestCase
{
    public function testParse_multiLineText(): void
    {
        $text = <<<TXT
#
fieldNum=2
###

// comments
# comments
id  The ID
invalid
 name   名称
 status   状态 1=待付款 2=待配送 3=配送中 8=待评价
TXT;
        $p = TextParser::new($text);
        $this->assertEquals(0, $p->fieldNum);

        $p->parse();

        $this->assertEquals(2, $p->fieldNum);
        $this->assertNotEmpty($data = $p->getData());
        vdump($data);
        $this->assertCount(3, $data);

        $this->assertNotEmpty($smp = $p->getStringMap(0, 1));
        vdump($smp);
        $this->assertCount(3, $smp);
        $this->assertEquals('名称', $smp['name']);
    }

    public function testParse_multiLineText1(): void
    {
        $text = <<<TXT
#
fields=[field,type,desc]
###

// comments
# comments
id int The ID
invalid
 name string  名称
 status  string  状态 1=待付款 2=待配送 3=配送中 8=待评价
TXT;
        $p = TextParser::new();
        $p->parse($text);

        $this->assertCount(3, $p->fields);
        $this->assertEquals(3, $p->fieldNum);
        $this->assertNotEmpty($data = $p->getData());
        $this->assertCount(3, $data);
        $this->assertEquals(['name', 'string', '名称'], $data[1]);

        $this->assertNotEmpty($data = $p->getData(true));
        $this->assertCount(3, $data);
        $this->assertEquals(['field' => 'name', 'type' => 'string', 'desc' => '名称'], $data[1]);
        vdump($data);

        $this->assertNotEmpty($mp = $p->getDataMap());
        $this->assertCount(3, $mp);
        $this->assertEquals(['name', 'string', '名称'], $mp['name']);
        vdump($mp);
        $this->assertNotEmpty($mp = $p->getDataMap(0, true));
        $this->assertCount(3, $mp);
        $this->assertEquals(['field' => 'name', 'type' => 'string', 'desc' => '名称'], $mp['name']);
        vdump($mp);
    }

    public function testSpaceSplitParser(): void
    {
        $p = TextParser::spaceSplitParser();
        $vs = $p(' fieldName   some field desc', 2);

        $this->assertNotEmpty($vs);
        $this->assertEquals(['fieldName', 'some field desc'], $vs);
        // vdump($vs);

        $vs = $p(' fieldName   some field desc', 3);
        $this->assertNotEmpty($vs);
        $this->assertEquals(['fieldName', 'some',  'field desc'], $vs);
        // vdump($vs);
    }
}
