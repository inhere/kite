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

    /**
     * @param string $viewPath
     * @param array $vars
     */
    public function renderOutput(string $viewPath, array $vars = []): void
    {
        $viewFile = $this->findTplFile($viewPath);

        echo $this->renderFile($viewFile, $vars);
    }
}
