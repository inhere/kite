<?php declare(strict_types=1);

namespace Inhere\Kite\Lib\Template\Compiler;

use function addslashes;
use function explode;
use function implode;
use function is_numeric;
use function preg_match;
use function preg_replace_callback;
use function strlen;
use function trim;

/**
 * class PregCompiler - compile template code to php code
 *
 * @author inhere
 */
class PregCompiler extends AbstractCompiler
{
    // add slashes tag name
    private string $openTagE = '\{\{';
    private string $closeTagE = '\}\}';

    /**
     * @return static
     */
    public static function new(): self
    {
        return new self();
    }

    /**
     * @param string $open
     * @param string $close
     *
     * @return $this
     */
    public function setOpenCloseTag(string $open, string $close): self
    {
        parent::setOpenCloseTag($open, $close);

        $this->openTagE  = addslashes($open);
        $this->closeTagE = addslashes($close);

        return $this;
    }

    /**
     * @param string $tplCode
     *
     * @return string
     */
    public function compile(string $tplCode): string
    {
        // Not contains open tag
        if (!str_contains($tplCode, $this->openTag)) {
            return $tplCode;
        }

        $this->prevTokenType = '';

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
            $tplCode,
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

        // special '}' -  if, for, foreach end char
        if ($trimmed === '}') {
            return self::PHP_TAG_OPEN . ' } ' . self::PHP_TAG_CLOSE;
        }

        $isInline = !str_contains($trimmed, "\n");
        $kwPattern = Token::getBlockNamePattern();

        // default is define statement.
        $type  = Token::T_DEFINE;
        $open  = self::PHP_TAG_OPEN . "\n";
        $close = ($isInline ? ' ' : "\n") . self::PHP_TAG_CLOSE;

        if ($trimmed[0] === '=') {
            $type = Token::T_ECHO;
            $open = self::PHP_TAG_ECHO;
        } elseif (str_starts_with($trimmed, 'echo ')) { // echo statement
            $type = Token::T_ECHO;
            $open = self::PHP_TAG_OPEN . ' ';
        } elseif ($isInline && ($tryType = Token::tryAloneToken($trimmed))) {
            // special alone token: break, default, continue
            $type = $tryType;
            $open = self::PHP_TAG_OPEN . ' ';

            // auto append end char ':'
            if ($type === Token::T_ELSE) {
                $close = ': ' . self::PHP_TAG_CLOSE;
            }
        } elseif (preg_match($kwPattern, $trimmed, $matches)) {
            // control block: if, for, foreach, define vars, etc
            $type = $matches[1];
            $open = self::PHP_TAG_OPEN . ' ';

            // auto fix pad some chars.
            if (Token::canAutoFixed($type)) {
                $endChar = $trimmed[strlen($trimmed) - 1];

                // not in raw php code AND end char != :
                if ($endChar !== '}' && $endChar !== '{' && $endChar !== ':') {
                    $close = ': ' . self::PHP_TAG_CLOSE;
                }
            }
        } elseif ($isInline && !str_contains($trimmed, '=')) {
            // inline and not define expr, as echo expr.
            $type = Token::T_ECHO;
            $open = self::PHP_TAG_ECHO1 . ' ';
        }

        // not need continue handle
        if (Token::isAloneToken($type)) {
            return $open . $trimmed . $close;
        }

        // TODO support custom directive tokens

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
}
