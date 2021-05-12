<?php declare(strict_types=1);

namespace Inhere\Kite\Common;

use InvalidArgumentException;
use Toolkit\FsUtil\File;
use Toolkit\Stdlib\Obj;
use function is_file;
use function strpos;

/**
 * Class HtmlTemplate
 *
 * @package Inhere\Kite\Common
 */
class HtmlTemplate extends TextTemplate
{
    /**
     * @var string
     */
    protected $viewsDir;

    /**
     * @var string[]
     */
    protected $allowExt = ['.html', '.phtml', '.php'];

    /**
     * manual set view files
     *
     * @var array
     */
    protected $viewsFiles = [];

    /**
     * @var array
     */
    protected $globalVars = [];

    /**
     * Class constructor.
     *
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        Obj::init($this, $config);
    }

    /**
     * @param string $viewPath
     * @param array  $vars
     *
     * @return string
     */
    public function render(string $viewPath, array $vars = []): string
    {
        $viewFile = $this->findViewFile($viewPath);

        return $this->renderFile($viewFile, $vars);
    }

    /**
     * @param string $viewPath
     * @param array  $vars
     */
    public function renderOutput(string $viewPath, array $vars = []): void
    {
        $viewFile = $this->findViewFile($viewPath);

        echo $this->renderFile($viewFile, $vars);
    }

    /**
     * @param string $viewName
     *
     * @return string
     */
    protected function findViewFile(string $viewName): string
    {
        if (isset($this->viewsFiles[$viewName])) {
            return $this->viewsFiles[$viewName];
        }

        $suffix = '';
        if (strpos($viewName, '.') > 0) {
            $suffix = File::getExtension($viewName);
        }

        $viewFile = $this->viewsDir . '/' . $viewName;

        // is an exists file
        if ($suffix) {
            if (is_file($viewFile)) {
                return $viewFile;
            }

            throw new InvalidArgumentException("the view file '$viewName' not found");
        }

        foreach ($this->allowExt as $ext) {
            $filename = $viewFile . $ext;
            if (is_file($filename)) {
                return $filename;
            }
        }

        throw new InvalidArgumentException("the view file '$viewName' not found");
    }

    /**
     * @param string $viewName
     * @param string $filePath
     */
    public function addViewFile(string $viewName, string $filePath): void
    {
        $this->viewsFiles[$viewName] = $filePath;
    }

    /**
     * @return array
     */
    public function getViewsFiles(): array
    {
        return $this->viewsFiles;
    }

    /**
     * @param array $viewsFiles
     */
    public function setViewsFiles(array $viewsFiles): void
    {
        $this->viewsFiles = $viewsFiles;
    }

    /**
     * @return string
     */
    public function getViewsDir(): string
    {
        return $this->viewsDir;
    }

    /**
     * @param string $viewsDir
     */
    public function setViewsDir(string $viewsDir): void
    {
        $this->viewsDir = $viewsDir;
    }

    /**
     * @return string[]
     */
    public function getAllowExt(): array
    {
        return $this->allowExt;
    }

    /**
     * @param string[] $allowExt
     */
    public function setAllowExt(array $allowExt): void
    {
        $this->allowExt = $allowExt;
    }

    /**
     * @return array
     */
    public function getGlobalVars(): array
    {
        return $this->globalVars;
    }

    /**
     * @param array $globalVars
     */
    public function setGlobalVars(array $globalVars): void
    {
        $this->globalVars = $globalVars;
    }
}
