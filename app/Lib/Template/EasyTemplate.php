<?php declare(strict_types=1);

namespace Inhere\Kite\Lib\Template;

use InvalidArgumentException;
use Toolkit\FsUtil\File;
use function addslashes;
use function explode;
use function file_exists;
use function implode;
use function is_numeric;
use function preg_match;
use function preg_replace_callback;
use function preg_split;
use function str_contains;
use function trim;
use function vdump;
use const PREG_SPLIT_NO_EMPTY;

/**
 * Class EasyTemplate
 *
 * @package Inhere\Kite\Model\Logic
 */
class EasyTemplate extends TextTemplate
{
    public const PHP_TAG_OPEN = '<?php';
    public const PHP_TAG_ECHO  = '<?';
    public const PHP_TAG_ECHO1  = '<?=';
    public const PHP_TAG_CLOSE = '?>';

    /**
     * @var string[]
     */
    protected array $allowExt = ['.php', '.tpl'];

    public string $openTag = '{{';
    public string $closeTag = '}}';

    // add slashes tag name
    private string $openTagE = '\{\{';
    private string $closeTagE = '\}\}';

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

        $openTagE  = $this->openTagE;
        $closeTagE = $this->closeTagE;
        // $pattern = "/$openTagE\s*(.+)$closeTagE/";

        $limit = -1;
        $flags = 0;
        // $flags = PREG_OFFSET_CAPTURE;
        // $flags = PREG_PATTERN_ORDER | PREG_SET_ORDER;

        // TIP: `.+` -> `.+?`
        // `?` - 非贪婪匹配; 若不加，会导致有多个相同标签时，第一个开始会匹配到最后一个的关闭
        return preg_replace_callback(
            "~$openTagE\s*(.+?)$closeTagE~s", // Amixu, iu, s
            function (array $matches) {
                // vdump($matches);
                return $this->parseCodeBlock($matches[1]);
            },
            $code,
            $limit,
            $count,
            $flags
        );
    }

    public const T_ECHO = 'echo';
    public const T_IF = 'if';
    public const T_FOR = 'for';
    public const T_FOREACH = 'foreach';
    public const T_SWITCH = 'switch';

    public const BLOCK_TOKENS = [
        'foreach',
        'endforeach',
        'for',
        'endfor',
        'if',
        'elseif',
        'else',
        'endif',
    ];

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

        $type = $trimmed[0];
        $left = self::PHP_TAG_OPEN . ' ';
        $right = self::PHP_TAG_CLOSE;

        $isInline = !str_contains($block, "\n");
        // ~^(if|elseif|else|endif|for|endfor|foreach|endforeach)~
        $kwPattern = '~^(' . implode('|', self::BLOCK_TOKENS) . ')~';

        // echo
        if ($type === '=' ) {
            $type = self::T_ECHO;
            $left = self::PHP_TAG_ECHO;
        } elseif (preg_match($kwPattern, $trimmed, $matches)) { // other: if, for, foreach, define vars, etc
            $type = $matches[1];
        } elseif ($isInline && !str_contains($block, '=')) {
            // auto add echo
            $type = self::T_ECHO;
            $left = self::PHP_TAG_ECHO1;
         }
vdump($type);
        // else code is define block

        $pattern = '~(' . implode(')|(', [
            '\$[\w.]+\w', // array key path.
        ]) . ')~';

        // https://www.php.net/manual/zh/reference.pcre.pattern.modifiers.php
        $block = preg_replace_callback($pattern, static function (array $matches) {
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
        }, $block);

        return $left . $block . $right;
    }


    /**
     * inside the if/elseif/else/for/foreach
     *
     * @var bool
     */
    private bool $insideIfFor = false;

    /**
     * inside the php tag
     *
     * @var bool
     */
    private bool $insideTag = false;

    /**
     * compile contents
     *
     * @param string $code
     *
     * @return string
     */
    public function compileCodeV2(string $code): string
    {
        // Not contains open tag
        if (!str_contains($code, $this->openTag)) {
            return $code;
        }

        $compiled = [];
        foreach (explode("\n", $code) as $line) {
            // empty line
            if (!$line || !trim($line)) {
                $compiled[] = $line;
                continue;
            }

            if (
                !$this->insideTag
                && (!str_contains($line, $this->openTag) || !str_contains($line, $this->closeTag))
            ) {
                $compiled[] = $line;
                continue;
            }

            // parse line
            $compiled[] = $this->analyzeLineChars($line);
        }

        return implode("\n", $compiled);
    }

    /**
     * @param string $line
     *
     * @return string
     */
    public function analyzeLineChars(string $line): string
    {
        $chars = preg_split('//u', $line, -1, PREG_SPLIT_NO_EMPTY);

        $prev = $next = 0;
        foreach ($chars as $i => $char) {

        }

        return '';
    }

}
