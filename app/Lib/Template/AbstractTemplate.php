<?php declare(strict_types=1);

namespace Inhere\Kite\Lib\Template;

use Inhere\Kite\Lib\Template\Contract\TemplateInterface;
use InvalidArgumentException;
use Toolkit\FsUtil\File;
use Toolkit\Stdlib\Obj;
use function is_file;
use function strpos;

/**
 * Class AbstractTemplate
 *
 * @author inhere
 * @package Inhere\Kite\Lib\Template
 */
abstract class AbstractTemplate implements TemplateInterface
{
    /**
     * @var array
     */
    protected array $globalVars = [];

    /**
     * allow template file ext list. should start with '.'
     *
     * @var string[]
     */
    protected array $allowExt = ['.php', '.tpl'];

    /**
     * @var string
     */
    protected string $tplDir = '';

    /**
     * manual set view files
     *
     * @var array
     */
    protected array $tplFiles = [];

    /**
     * @return static
     */
    public static function new(array $config = []): self
    {
        return new static($config);
    }

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
     * @param string $tplName
     *
     * @return string
     */
    protected function findTplFile(string $tplName): string
    {
        if (is_file($tplName)) {
            return $tplName;
        }

        if (isset($this->tplFiles[$tplName])) {
            return $this->tplFiles[$tplName];
        }

        if (!$this->tplDir) {
            throw new InvalidArgumentException("no such template file: $tplName");
        }

        $suffix  = '';
        $tplFile = $this->tplDir . '/' . $tplName;

        if (strpos($tplName, '.') > 0) {
            $suffix = File::getExtension($tplName);
        }

        // is an exists file
        if ($suffix) {
            if (is_file($tplFile)) {
                return $tplFile;
            }
        } else {
            foreach ($this->allowExt as $ext) {
                $filename = $tplFile . $ext;
                if (is_file($filename)) {
                    return $filename;
                }
            }
        }

        throw new InvalidArgumentException("no such template file: $tplName");
    }

    /**
     * @param string $tplName
     * @param string $filePath
     */
    public function addTplFile(string $tplName, string $filePath): void
    {
        $this->tplFiles[$tplName] = $filePath;
    }

    /**
     * @return array
     */
    public function getTplFiles(): array
    {
        return $this->tplFiles;
    }

    /**
     * @param array $tplFiles
     */
    public function setTplFiles(array $tplFiles): void
    {
        $this->tplFiles = $tplFiles;
    }

    /**
     * @return string
     */
    public function getTplDir(): string
    {
        return $this->tplDir;
    }

    /**
     * @param string $tplDir
     */
    public function setTplDir(string $tplDir): void
    {
        $this->tplDir = $tplDir;
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
