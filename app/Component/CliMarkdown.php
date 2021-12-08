<?php declare(strict_types=1);

namespace Inhere\Kite\Component;

use cebe\markdown\GithubMarkdown;
use Toolkit\Cli\Color;
use Toolkit\Cli\ColorTag;
use function array_merge;
use function array_sum;
use function count;
use function explode;
use function implode;
use function ltrim;
use function mb_strlen;
use function sprintf;
use function str_pad;
use function str_repeat;
use function str_replace;
use function substr;
use function trim;
use function ucwords;

/**
 * Class CliMarkdown
 *
 * @package Inhere\Kite\Component
 * @link    https://github.com/charmbracelet/glow color refer
 */
class CliMarkdown extends GithubMarkdown
{
    public const NL  = "\n";
    public const NL2 = "\n\n";

    public const POINT = '●•○◦◉◎⦿✓✔︎✕✖︎✗';

    public const LANG_EN = 'en';

    public const GITHUB_HOST = 'https://github.com/';

    public const THEME_DEFAULT = [
        'headline'   => 'lightBlue',
        'paragraph'  => '',
        'list'       => '',
        'image'      => 'info',
        'link'       => 'underscore',
        'code'       => 'brown',
        'quote'      => 'cyan',
        'strong'     => 'bold',
        'inlineCode' => 'lightRedEx',
    ];

    /**
     * The document content language
     *
     * @var string
     */
    private string $lang;

    /**
     * @var array
     */
    private array $theme = self::THEME_DEFAULT;

    /**
     * Class constructor.
     *
     * @param string $lang
     */
    public function __construct(string $lang = '')
    {
        $this->lang = $lang;
    }

    /**
     * @param string $text
     *
     * @return string
     */
    public function parse($text): string
    {
        $parsed = parent::parse($text);

        return str_replace(["\n\n\n", "\n\n\n\n"], "\n\n", ltrim($parsed));
    }

    /**
     * @param string $text
     *
     * @return string
     */
    public function render(string $text): string
    {
        $parsed = $this->parse($text);

        return Color::parseTag($parsed);
    }

    /**
     * @param array $block
     *
     * @return string
     */
    protected function renderHeadline($block): string
    {
        $level = (int)$block['level'];

        $prefix = str_repeat('#', $level);
        $title  = $this->renderAbsy($block['content']);

        if ($this->lang === self::LANG_EN) {
            $title = ucwords($title);
        }

        $hlText = $prefix . ' ' . $title;

        return self::NL . ColorTag::add($hlText, $this->theme['headline']) . self::NL2;
    }

    /**
     * @param array $block
     *
     * @return string
     */
    protected function renderParagraph($block): string
    {
        return self::NL . $this->renderAbsy($block['content']) . self::NL;
    }

    /**
     * Renders a list
     *
     * @param array $block
     *
     * @return string
     */
    protected function renderList($block): string
    {
        $output = self::NL;

        foreach ($block['items'] as $itemLines) {
            $output .= '● ' . $this->renderAbsy($itemLines) . "\n";
        }

        return $output . self::NL2;
    }

    /**
     * @param array $block
     *
     * @return string
     */
    protected function renderTable($block): string
    {
        $head = $body = '';
        // $cols = $block['cols'];

        $tabInfo   = ['width' => 60];
        $colWidths = [];
        foreach ($block['rows'] as $row) {
            foreach ($row as $c => $cell) {
                $cellLen = $this->getCellWith($cell);

                if (!isset($tabInfo[$c])) {
                    $colWidths[$c] = 16;
                }

                $colWidths[$c] = $this->compareMax($cellLen, $colWidths[$c]);
            }
        }

        $colCount = count($colWidths);
        $tabWidth = (int)array_sum($colWidths);

        $first  = true;
        $splits = [];
        foreach ($block['rows'] as $row) {
            // $cellTag = $first ? 'th' : 'td';
            $tds = [];
            foreach ($row as $c => $cell) {
                $cellLen = $colWidths[$c];

                // ︱｜｜—―￣==＝＝▪▪▭▭▃▃▄▄▁▁▕▏▎┇╇══
                if ($first) {
                    $splits[] = str_pad('=', $cellLen + 1, '=');
                }

                $lastIdx = count($cell) - 1;
                // padding space to last item contents.
                foreach ($cell as $idx => &$item) {
                    if ($lastIdx === $idx) {
                        $item[1] = str_pad($item[1], $cellLen);
                    } else {
                        $cellLen -= mb_strlen($item[1]);
                    }
                }
                unset($item);
                // vdump($cellLen, $lastIdx, $cell);

                $tds[] = trim($this->renderAbsy($cell), "\n\r");
            }

            $tdsStr = implode(' | ', $tds);
            if ($first) {
                $head .= implode('=', $splits) . "\n$tdsStr\n" . implode('|', $splits) . "\n";
            } else {
                $body .= "$tdsStr\n";
            }
            $first = false;
        }

        // return $this->composeTable($head, $body);
        return $head . $body . str_pad('=', $tabWidth + $colCount + 1, '=') . self::NL;
    }

    /**
     * @param array $cellElems
     *
     * @return int
     */
    protected function getCellWith(array $cellElems): int
    {
        $width = 0;
        foreach ($cellElems as $elem) {
            $width += mb_strlen($elem[1] ?? '');
        }

        return $width;
    }

    /**
     * @param array $block
     *
     * @return string
     */
    protected function renderLink($block): string
    {
        return ColorTag::add('♆ ' . $block['orig'], $this->theme['link']);
    }

    /**
     * @param $block
     *
     * @return string
     */
    protected function renderUrl($block): string
    {
        return parent::renderUrl($block);
    }

    /**
     * @param $block
     *
     * @return string
     */
    protected function renderAutoUrl($block): string
    {
        $tag = $this->theme['link'];
        $url = $text = $block[1];

        if (str_contains($url, self::GITHUB_HOST)) {
            $text = substr($text, 19);
        }

        return sprintf('<%s>[%s]%s</%s>', $tag, $text, $url, $tag);
    }

    /**
     * @param $block
     *
     * @return string
     */
    protected function renderImage($block): string
    {
        return self::NL . Color::addTag('▨ ' . $block['orig'], $this->theme['image']);
    }

    /**
     * @param array $block
     *
     * @return string
     */
    protected function renderQuote($block): string
    {
        // ¶ §
        $prefix  = Color::render('¶ ', [Color::FG_GREEN, Color::BOLD]);
        $content = ltrim($this->renderAbsy($block['content']));

        return self::NL . $prefix . ColorTag::add($content, $this->theme['quote']);
    }

    /**
     * @param array $block
     *
     * @return string
     */
    protected function renderCode($block): string
    {
        $lines = explode(self::NL, $block['content']);
        $text  = implode("\n    ", $lines);

        return "\n    " . ColorTag::add($text, $this->theme['code']) . self::NL2;
    }

    /**
     * @param array $block
     *
     * @return string
     */
    protected function renderInlineCode($block): string
    {
        return ColorTag::add($block[1], $this->theme['inlineCode']);
    }

    /**
     * @param $block
     *
     * @return string
     */
    protected function renderStrong($block): string
    {
        $text = $this->renderAbsy($block[1]);

        return ColorTag::add("**$text**", $this->theme['strong']);

        // return self::NL . ColorTag::add("**$text**", $this->theme['strong']) . self::NL;
    }

    /**
     * @param array $block
     *
     * @return string
     */
    protected function renderText($block): string
    {
        return $block[1];
    }

    /**
     * @return array
     */
    public function getTheme(): array
    {
        return $this->theme;
    }

    /**
     * @param array $theme
     */
    public function setTheme(array $theme): void
    {
        $this->theme = array_merge($this->theme, $theme);
    }

    /**
     * @param int $len1
     * @param int $len2
     *
     * @return int
     */
    private function compareMax(int $len1, int $len2): int
    {
        return $len1 > $len2 ? $len1 : $len2;
    }
}
