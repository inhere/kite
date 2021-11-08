<?php declare(strict_types=1);

namespace Inhere\KiteTest\Lib\Template;

use Inhere\Kite\Lib\Template\Compiler\PregCompiler;
use Inhere\Kite\Lib\Template\Compiler\Token;
use Inhere\KiteTest\BaseKiteTestCase;
use function preg_match;

/**
 * class PregCompilerTest
 *
 * @author inhere
 */
class PregCompilerTest extends BaseKiteTestCase
{
    public function testCompile_empty_noTag():void
    {
        $p = new PregCompiler();

        $compiled = $p->compile('');
        $this->assertEquals('', $compiled);

        $compiled = $p->compile('no tpl tags');
        $this->assertEquals('no tpl tags', $compiled);
    }

    public function testToken_getBlockNamePattern():void
    {
        $tests = [
            // if
            ['if ', 'if'],
            ['if(', 'if'],
            // - error
            ['if', ''],
            ['if3', ''],
            ['ifa', ''],
            ['ifA', ''],
            ['if_', ''],
            ['if-', ''],
            // foreach
            ['foreach ', 'foreach'],
            ['foreach(', 'foreach'],
            // - error
            ['foreach', ''],
            ['foreachA', ''],
            // special
            ['break ', 'break'],
            ['default ', Token::T_DEFAULT],
            ['continue ', Token::T_CONTINUE],
            // - error
            ['break', ''],
            ['default', ''],
            ['continue', ''],
        ];

        $pattern = Token::getBlockNamePattern();
        foreach ($tests as [$in, $out]) {
            $ret = preg_match($pattern, $in, $matches);
            if ($out) {
                $this->assertEquals(1, $ret);
                $this->assertEquals($out, $matches[1]);
            } else {
                $this->assertEquals(0, $ret);
            }
        }
    }

    public function testCompileCode_inline_echo():void
    {
        $p = new PregCompiler();

        $simpleTests = [
            ['{{ "a" . "b" }}', '<?= "a" . "b" ?>'],
            ['{{ $name }}', '<?= $name ?>'],
            ['{{ $name ?: "inhere" }}', '<?= $name ?: "inhere" ?>'],
            ['{{ $name ?? "inhere" }}', '<?= $name ?? "inhere" ?>'],
            ['{{ some_func() }}', '<?= some_func() ?>'],
            ['{{ $this->include("header.tpl") }}', '<?= $this->include("header.tpl") ?>'],
        ];
        foreach ($simpleTests as [$in, $out]) {
            $this->assertEquals($out, $p->compile($in));
        }

        $tplCode = <<<'TPL'

{{= $ctx.pkgName ?? "org.example.entity" }}

TPL;
        $compiled = $p->compile($tplCode);
        // vdump($tplCode, $compiled);
        $this->assertNotEmpty($compiled);
        $this->assertEquals(<<<'CODE'

<?= $ctx['pkgName'] ?? "org.example.entity" ?>

CODE
            ,$compiled);

        $tplCode = <<<'TPL'
{{= $ctx->pkgName ?? "org.example.entity" }}
TPL;
        $compiled = $p->compile($tplCode);
        // vdump($tplCode, $compiled);
        $this->assertNotEmpty($compiled);
        $this->assertEquals(<<<'CODE'
<?= $ctx->pkgName ?? "org.example.entity" ?>
CODE
            ,$compiled);
    }

    public function testCompile_customDirective():void
    {
        $p = new PregCompiler();
        $p->addDirective('include', function (string $body, string $name) {
            return '$this->' . $name . $body;
        });

        $tests = [
            ['{{ include("header.tpl") }}', '<?php $this->include("header.tpl") ?>'],
            ['{{ include("header.tpl", [
   "key1" => "value1",
]) }}', '<?php
$this->include("header.tpl", [
   "key1" => "value1",
])
?>'],
        ];
        foreach ($tests as [$in, $out]) {
            $this->assertEquals($out, $p->compile($in));
        }
    }

    public function testCompile_if_block():void
    {
        $p = new PregCompiler();

        $tests = [
            [
                '{{if ($a < 4) }} hi {{endif}}',
                '<?php if ($a < 4): ?> hi <?php endif ?>',
            ],
            [
                '{{if ($a < 4) { }} hi {{ } }}',
                '<?php if ($a < 4) { ?> hi <?php } ?>',
            ],
            [
                '{{if ($a < 4) }}
hi
{{endif}}',
                '<?php if ($a < 4): ?>
hi
<?php endif ?>',
            ],
            [
                '<?php if ($a < 4): ?> hi <?php endif ?>', // raw
                '<?php if ($a < 4): ?> hi <?php endif ?>',
            ],
        ];
        foreach ($tests as [$in, $out]) {
            $this->assertEquals($out, $p->compile($in));
        }
    }

    public function testCompileCode_ml_define():void
    {
        $p = new PregCompiler();

        $code = <<<'CODE'
{{

$a = random_int(1, 10);
}}
CODE;
        $compiled = $p->compile($code);
        $this->assertEquals(<<<'CODE'
<?php $a = random_int(1, 10); ?>
CODE
            ,$compiled);

        $code = <<<'CODE'
{{
// comments
$a = random_int(1, 10);
}}
CODE;
        $compiled = $p->compile($code);
        $this->assertEquals(<<<'CODE'
<?php
// comments
$a = random_int(1, 10);
?>
CODE
            ,$compiled);
    }

}
