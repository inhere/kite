<?php declare(strict_types=1);

namespace Inhere\PTool\Common;

use cebe\markdown\GithubMarkdown;
use Toolkit\Cli\ColorTag;

/**
 * Class CliMarkdown
 *
 * @package Inhere\PTool\Common
 * @link https://github.com/charmbracelet/glow color refer
 */
class CliMarkdown extends GithubMarkdown
{
    /**
     * @param $block
     *
     * @return string|void
     */
    protected function renderCode($block)
    {
        return ColorTag::add($block['content'], 'yellow');
    }

    protected function renderHeadline($block): string
    {
        return ColorTag::add($block['content'], 'lightBlue') . "\n";
    }

    /**
     * @param $block
     *
     * @return string
     */
    protected function renderParagraph($block): string
    {
        return $block['content'] . "\n";
    }
}
