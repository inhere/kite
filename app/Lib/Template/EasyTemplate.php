<?php declare(strict_types=1);

namespace Inhere\Kite\Lib\Template;

use Inhere\Kite\Lib\Template\Compiler\PregCompiler;
use Inhere\Kite\Lib\Template\Contract\CompilerInterface;
use Inhere\Kite\Lib\Template\Contract\EasyTemplateInterface;

/**
 * Class EasyTemplate
 *
 * @author inhere
 */
class EasyTemplate extends TextTemplate implements EasyTemplateInterface
{
    /**
     * @var CompilerInterface
     */
    private CompilerInterface $compiler;

    /**
     * Class constructor.
     *
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        parent::__construct($config);

        $this->compiler = new PregCompiler();
        $this->compiler->addDirective(
            'include',
            function (string $body, string $name) {
                return '$this->' . $name . $body;
            }
        );
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
     * @param string $tplCode
     * @param array $tplVars
     *
     * @return string
     */
    public function renderString(string $tplCode, array $tplVars): string
    {
        $tplCode = $this->compiler->compile($tplCode);

        return parent::renderString($tplCode, $tplVars);
    }

    /**
     * @param string $tplFile
     * @param array $tplVars
     */
    protected function include(string $tplFile, array $tplVars): void
    {
        $phpFile = $this->compileFile($tplFile);

        echo $this->doRenderFile($phpFile, $tplVars);
    }

    /**
     * @param string $tplFile
     * @param array $tplVars
     *
     * @return string
     */
    protected function renderInclude(string $tplFile, array $tplVars): string
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
        $tplFile = $this->findTplFile($tplFile);

        // compile contents
        $tplCode = $this->compiler->compileFile($tplFile);

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
        return $this->compiler->compile($code);
    }

    /**
     * @param string $name
     * @param callable $filter
     *
     * @return $this
     */
    public function addFilter(string $name, callable $filter): self
    {
        $this->compiler->addFilter($name, $filter);
        return $this;
    }

    /**
     * @param string $name
     * @param callable $handler
     *
     * @return $this
     */
    public function addDirective(string $name, callable $handler): self
    {
        $this->compiler->addDirective($name, $handler);
        return $this;
    }

    /**
     * @param string $open
     * @param string $close
     *
     * @return $this
     */
    public function setOpenCloseTag(string $open, string $close): self
    {
        $this->getCompiler()->setOpenCloseTag($open, $close);
        return $this;
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
        return $this->compiler;
    }
}
