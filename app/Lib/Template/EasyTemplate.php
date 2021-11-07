<?php declare(strict_types=1);

namespace Inhere\Kite\Lib\Template;

use Inhere\Kite\Lib\Template\Compiler\PregCompiler;
use Inhere\Kite\Lib\Template\Compiler\Token;
use Inhere\Kite\Lib\Template\Contract\CompilerInterface;
use Inhere\Kite\Lib\Template\Contract\EasyTemplateInterface;
use InvalidArgumentException;
use Toolkit\FsUtil\File;
use function addslashes;
use function explode;
use function file_exists;
use function implode;
use function in_array;
use function is_numeric;
use function preg_match;
use function preg_replace_callback;
use function str_contains;
use function str_starts_with;
use function strlen;
use function trim;
use function vdump;

/**
 * Class EasyTemplate
 *
 * @author inhere
 */
class EasyTemplate extends TextTemplate implements EasyTemplateInterface
{
    public const PHP_TAG_OPEN  = '<?php';
    public const PHP_TAG_ECHO  = '<?';
    public const PHP_TAG_ECHO1 = '<?=';
    public const PHP_TAG_CLOSE = '?>';

    /**
     * @var string[]
     */
    protected array $allowExt = ['.php', '.tpl'];

    /**
     * @var CompilerInterface
     */
    private CompilerInterface $compiler;

    public string $openTag = '{{';
    public string $closeTag = '}}';

    // add slashes tag name
    private string $openTagE = '\{\{';
    private string $closeTagE = '\}\}';

    /**
     * custom directive, control statement token.
     *
     * eg: implement include()
     *
     * @var array
     */
    public array $customTokens = [];

    /**
     * @param string $open
     * @param string $close
     *
     * @return $this
     */
    public function setOpenCloseTag(string $open, string $close): self
    {
        $this->openTag  = $open;
        $this->openTagE = addslashes($open);

        $this->closeTag  = $close;
        $this->closeTagE = addslashes($close);

        return $this;
    }

    /**
     * @param string $tplCode
     * @param array $tplVars
     *
     * @return string
     */
    public function renderString(string $tplCode, array $tplVars): string
    {
        $tplCode = $this->compileCode($tplCode);

        return parent::renderString($tplCode, $tplVars);
    }

    /**
     * @param string $tplFile
     * @param array $tplVars
     *
     * @return string
     */
    public function renderFile(string $tplFile, array $tplVars): string
    {
        $phpFile = $this->compileFile($tplFile);

        return $this->doRenderFile($phpFile, $tplVars);
    }

    /**
     * @param string $tplFile
     *
     * @return string
     */
    public function compileFile(string $tplFile): string
    {
        if (!file_exists($tplFile)) {
            throw new InvalidArgumentException('no such template file:' . $tplFile);
        }

        $tplCode = File::readAll($tplFile);
        // compile contents
        $tplCode = $this->compileCode($tplCode);

        // generate temp php file
        return $this->genTempPhpFile($tplCode);
    }

    /**
     * compile contents
     *
     * @param string $code
     *
     * @return string
     */
    public function compileCode(string $code): string
    {
        // Not contains open tag
        if (!str_contains($code, $this->openTag)) {
            return $code;
        }

        // $compiler = $this->getCompiler();
        // $compiler->compile($code);

        $openTagE  = $this->openTagE;
        $closeTagE = $this->closeTagE;

        $flags = 0;
        // $flags = PREG_OFFSET_CAPTURE;
        // $flags = PREG_PATTERN_ORDER | PREG_SET_ORDER;

        // TIP: `.+` -> `.+?`
        // `?` - 非贪婪匹配; 若不加，会导致有多个相同标签时，第一个开始会匹配到最后一个的关闭
        return preg_replace_callback(
            "~$openTagE\s*(.+?)$closeTagE~s", // Amixu, iu, s
            function (array $matches) {
                return $this->parseCodeBlock($matches[1]);
            },
            $code,
            -1,
            $count,
            $flags
        );
    }

    /**
     * parse code block string.
     *
     * - '=': echo
     * - '-': trim
     * - 'if'
     * - 'for'
     * - 'foreach'
     * - 'switch'
     *
     * @param string $block
     *
     * @return string
     */
    public function parseCodeBlock(string $block): string
    {
        if (!$trimmed = trim($block)) {
            return $block;
        }

        $isInline = !str_contains($trimmed, "\n");
        // ~^(if|elseif|else|endif|for|endfor|foreach|endforeach)~
        $kwPattern = Token::getBlockNamePattern();

        // default is define statement.
        $type = Token::T_DEFINE;
        $open = self::PHP_TAG_OPEN . "\n";
        $close = ($isInline ? ' ' : "\n" ) . self::PHP_TAG_CLOSE;

        // echo statement
        if ($trimmed[0] === '=') {
            $type = Token::T_ECHO;
            $open = self::PHP_TAG_ECHO;
        } elseif (str_starts_with($trimmed, 'echo')) { // echo statement
            $type = Token::T_ECHO;
            $open = self::PHP_TAG_OPEN . ' ';
        } elseif ($isInline && ($tryType = Token::tryAloneToken($trimmed))) {
            // special alone token: break, default, continue
            $type = $tryType;
            $open = self::PHP_TAG_OPEN . ' ';
            // auto append end char ':'
            $close = ': ' . self::PHP_TAG_CLOSE;
        } elseif (preg_match($kwPattern, $trimmed, $matches)) {
            // control block: if, for, foreach, define vars, etc
            $type = $matches[1];
            $open = self::PHP_TAG_OPEN . ' ';

            // auto fix pad some chars.
            if (Token::canAutoFixed($type)) {
                $endChar = $trimmed[strlen($trimmed)-1];

                if ($endChar !== '}' && $endChar !== ':') {
                    $close = ': ' . self::PHP_TAG_CLOSE;
                }
            }
        } elseif ($isInline && !str_contains($block, '=')) {
            // inline and not define expr, as echo expr.
            $type = Token::T_ECHO;
            $open = self::PHP_TAG_ECHO1;
        }

        // handle
        // - convert $ctx.top.sub to $ctx[top][sub]
        $pattern = '~(' . implode(')|(', [
                '\$[\w.]+\w', // array key path.
            ]) . ')~';

        // https://www.php.net/manual/zh/reference.pcre.pattern.modifiers.php
        $trimmed = preg_replace_callback($pattern, static function (array $matches) {
            $varName = $matches[0];
            // convert $ctx.top.sub to $ctx[top][sub]
            if (str_contains($varName, '.')) {
                $nodes = [];
                foreach (explode('.', $varName) as $key) {
                    if ($key[0] === '$') {
                        $nodes[] = $key;
                    } else {
                        $nodes[] = is_numeric($key) ? "[$key]" : "['$key']";
                    }
                }

                $varName = implode('', $nodes);
            }

            return $varName;
        }, $trimmed);

        return $open . $trimmed . $close;
    }

    /**
     * @param CompilerInterface $compiler
     *
     * @return EasyTemplate
     */
    public function setCompiler(CompilerInterface $compiler): self
    {
        $this->compiler = $compiler;
        return $this;
    }

    /**
     * @return CompilerInterface
     */
    public function getCompiler(): CompilerInterface
    {
        if (!$this->compiler) {
            $this->compiler = new PregCompiler();
        }

        return $this->compiler;
    }

}
