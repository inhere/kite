<?php declare(strict_types=1);

namespace Inhere\PTool\Common;

use cebe\markdown\GithubMarkdown;
use Toolkit\Cli\ColorTag;
use function explode;
use function implode;
use function str_repeat;
use function ucwords;

/**
 * Class CliMarkdown
 *
 * @package Inhere\PTool\Common
 * @link https://github.com/charmbracelet/glow color refer
 */
class CliMarkdown extends GithubMarkdown
{
    public const NL = "\n";
    public const NL2 = "\n\n";

    public const POINT = '●•○◦◉◎⦿✓✔︎✕✖︎✗';

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
        $hlText = $prefix . ' ' .  ucwords($title);

        return self::NL . ColorTag::add($hlText, 'lightBlue') . self::NL2;
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

        foreach ($block['items'] as $item => $itemLines) {
            $output .= '● ' . $this->renderAbsy($itemLines). "\n";
        }

        return $output . self::NL2;
    }

    /**
     * @param $block
     *
     * @return mixed|string
     */
    protected function renderLink($block)
    {
        // \var_dump($block);
        return ColorTag::add($block['orig'], 'info');
    }

    /**
     * @param array $block
     *
     * @return string|void
     */
    protected function renderCode($block)
    {
        $lines = explode(self::NL, $block['content']);
        $text  = implode("\n    ", $lines);

        return "\n    " . ColorTag::add($text, 'brown') . self::NL2;
    }

    /**
     * @param array $block
     *
     * @return string
     */
    protected function renderInlineCode($block): string
    {
        return ColorTag::add($block[1], 'light_red_ex');
    }
}
