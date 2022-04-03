<?php declare(strict_types=1);

namespace Inhere\Kite\Lib\Generate;

use Inhere\Kite\Helper\KiteUtil;
use Inhere\Kite\Lib\Generate\Json\JsonField;
use InvalidArgumentException;
use Toolkit\FsUtil\File;
use Toolkit\Stdlib\Obj;
use Toolkit\Stdlib\OS;
use function array_merge;
use function date;
use function dirname;
use function is_file;

/**
 * class AbstractGenCode
 *
 * @author inhere
 */
abstract class AbstractGenCode
{
    /**
     * @var string
     */
    public string $tplDir = '';

    /**
     * @var string
     */
    public string $tplFile = '';

    /**
     * @var callable
     */
    protected $pathResolver;

    /**
     * contexts vars for render template
     *
     * @var array
     */
    protected array $contexts = [];

    /**
     * @var string
     */
    public string $className = 'YourClass';

    /**
     * @var array<string, JsonField>
     */
    protected array $fields = [];

    /**
     * @return string
     */
    public function getLang(): string
    {
        return 'java';
    }

    /**
     * @return string
     */
    public function generate(): string
    {
        // defaults
        $this->contexts['className'] = $this->className;

        return $this->renderTplText();
    }

    /**
     * @return string
     */
    protected function renderTplText(): string
    {
        $tplText  = $this->readSourceFromFile();
        $settings = array_merge([
            'lang' => $this->getLang(),
            'user' => OS::getUserName(),
            'date' => date('Y-m-d'),
        ], $this->contexts);

        $settings['fields'] = $this->fields;

        return KiteUtil::newTplEngine([
            'tplDir' => $this->tplDir,
        ])->renderString($tplText, $settings);
    }

    /**
     * @param string $outFile
     *
     * @return bool
     */
    public function generateTo(string $outFile): bool
    {
        $results = $this->generate();
        $outFile = $this->resolvePath($outFile);

        File::mkdir(dirname($outFile));
        return File::write($results, $outFile);
    }

    /**
     * @param array $config
     *
     * @return self
     */
    public function configThis(array $config): self
    {
        Obj::init($this, $config);

        return $this;
    }

    /**
     * @return string
     */
    protected function readSourceFromFile(): string
    {
        $tplFile = $this->tplFile;
        if (!$tplFile) {
            return '';
        }

        $tplFile = $this->resolvePath($tplFile);

        // check from tplDir
        if ($this->tplDir && !is_file($tplFile)) {
            $tplDir  = $this->resolvePath($this->tplDir);
            $dirFile = File::joinPath($tplDir, $tplFile);

            if (!is_file($dirFile)) {
                throw new InvalidArgumentException("No such file: $tplFile");
            }

            $tplFile = $dirFile;
        }

        return File::readAll($tplFile);
    }

    /**
     * @param string $filePath
     *
     * @return string
     */
    public function resolvePath(string $filePath): string
    {
        if ($fn = $this->pathResolver) {
            return $fn($filePath);
        }

        // return Kite::alias($filePath);
        return $filePath;
    }

    /**
     * @param string $name
     * @param mixed $value
     *
     * @return $this
     */
    public function addTplVar(string $name, mixed $value): self
    {
        $this->contexts[$name] = $value;
        return $this;
    }

    /**
     * @param array $contexts
     *
     * @return AbstractJsonToCode
     */
    public function setContexts(array $contexts): self
    {
        if ($contexts) {
            $this->contexts = array_merge($this->contexts, $contexts);
        }

        return $this;
    }

    /**
     * @return array
     */
    public function getFields(): array
    {
        return $this->fields;
    }

    /**
     * @param JsonField[] $fields
     *
     * @return AbstractGenCode
     */
    public function setFields(array $fields): self
    {
        $this->fields = $fields;
        return $this;
    }

    /**
     * @param callable $pathResolver
     *
     * @return AbstractJsonToCode
     */
    public function setPathResolver(callable $pathResolver): self
    {
        $this->pathResolver = $pathResolver;
        return $this;
    }

    /**
     * @param string $className
     *
     * @return AbstractJsonToCode
     */
    public function setClassName(string $className): self
    {
        if ($className) {
            $this->className = $className;
        }

        return $this;
    }
}
