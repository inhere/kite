<?php declare(strict_types=1);

namespace Inhere\Kite\Lib\Template;

/**
 * Class HtmlTemplate
 *
 * @package Inhere\Kite\Lib
 */
class HtmlTemplate extends TextTemplate
{
    /**
     * @var string[]
     */
    protected array $allowExt = ['.html', '.phtml', '.php'];
}
