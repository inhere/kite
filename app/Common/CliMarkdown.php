<?php declare(strict_types=1);

namespace Inhere\PTool\Common;

use cebe\markdown\GithubMarkdown;
use Toolkit\Cli\ColorTag;
use function str_repeat;

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

    /**
     * @param array $block
     *
     * @return string
     */
    protected function renderHeadline($block): string
    {
        $level = (int)$block['level'];

        $prefix = str_repeat('#', $level);
        $hlText = $prefix . ' ' .  $this->renderAbsy($block['content']);

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
     * @param array $block
     *
     * @return string|void
     */
    protected function renderCode($block)
    {
        return "\n    " . ColorTag::add($block['content'], 'brown') . self::NL2;
    }

    /**
     * @param array $block
     *
     * @return string
     */
    protected function renderInlineCode($block): string
    {
        return ColorTag::add($block[1], 'magenta');
    }
}
