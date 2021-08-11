<?php declare(strict_types=1);

namespace Inhere\Kite\Common;

use cebe\markdown\GithubMarkdown;
use Toolkit\Cli\Color;
use Toolkit\Cli\ColorTag;
use function array_merge;
use function explode;
use function implode;
use function ltrim;
use function sprintf;
use function str_repeat;
use function str_replace;
use function strpos;
use function substr;
use function trim;
use function ucwords;

/**
 * Class CliMarkdown
 *
 * @package Inhere\Kite\Common
 * @link    https://github.com/charmbracelet/glow color refer
 */
class CliMarkdown extends GithubMarkdown
{
    public const NL  = "\n";
    public const NL2 = "\n\n";

    public const POINT = '●•○◦◉◎⦿✓✔︎✕✖︎✗';

    public const LANG_EN = 'en';

    public const GITHUB_HOST = 'https://github.com/';

    public const THEME_LIGHT = [
        'headline'   => 'lightBlue',
        'paragraph'  => '',
        'list'       => '',
        'link'       => 'info',
        'code'       => 'brown',
        'quote'      => 'cyan',
        'strong'     => 'bold',
        'inlineCode' => 'lightRedEx',
    ];

    public const THEME_DARK = [

    ];

    /**
     * The document content language
     *
     * @var string
     */
    private $lang;

    /**
     * @var array
     */
    private $theme = self::THEME_LIGHT;

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
        return $this->renderAbsy($block['content']) . self::NL;
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
        $head = '';
        $body = '';
        $cols = $block['cols'];

        $first = true;
        foreach ($block['rows'] as $row) {
            $cellTag = $first ? 'th' : 'td';
            $tds     = '';
            foreach ($row as $c => $cell) {
                $align = empty($cols[$c]) ? '' : ' align="' . $cols[$c] . '"';
                $tds   .= "<$cellTag$align>" . trim($this->renderAbsy($cell)) . "</$cellTag>";
            }

            if ($first) {
                $head .= "<tr>$tds</tr>\n";
            } else {
                $body .= "<tr>$tds</tr>\n";
            }
            $first = false;
        }

        return $this->composeTable($head, $body);
    }

    /**
     * @param string $head
     * @param string $body
     *
     * @return string
     */
    protected function composeTable($head, $body): string
    {
        $table = <<<TXT
$head
========|==============
$body
TXT;

        return $table;
    }

    /**
     * @param array $block
     *
     * @return string
     */
    protected function renderLink($block): string
    {
        return ColorTag::add($block['orig'], $this->theme['link']);
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

        if (strpos($url, self::GITHUB_HOST) !== false) {
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
        return sprintf('%s', $block['orig']);
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

        return self::NL . ColorTag::add("**$text**", $this->theme['strong']) . self::NL;
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
}
