<?php declare(strict_types=1);

namespace Inhere\Kite\Lib\Generate;

use Inhere\Kite\Helper\KiteUtil;
use Inhere\Kite\Lib\Defines\DataField\JsonField;
use InvalidArgumentException;
use Toolkit\FsUtil\File;
use Toolkit\Stdlib\Obj;
use Toolkit\Stdlib\OS;
use Toolkit\Stdlib\Str;
use function array_merge;
use function date;
use function dirname;
use function is_file;
use function strpos;

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
     * @var bool
     */
    protected bool $prepared = false;

    /**
     * @return string
     */
    public function getLang(): string
    {
        return 'java';
    }

    public function prepareContext(): void
    {
        // defaults
        $this->addContexts([
            'className' => $this->className,
            'package'   => 'YOUR.PKG.NAME',
            'modulePkg' => 'MODULE_PKG',
            'pkgName'   => 'PKG_NAME',
            'subPkg'    => 'SUB_PKG',
        ]);
    }

    /**
     * @return AbstractJsonToCode
     */
    public function prepare(): self
    {
        if ($this->prepared) {
            return $this;
        }

        $this->prepared = true;
        $this->prepareContext();

        return $this;
    }

    /**
     * @return string
     */
    public function generate(): string
    {
        $this->prepare();

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
     * load tpl vars from strings
     *
     * - each string format: KEY:VALUE
     *
     * @param array $ss
     *
     * @return $this
     */
    public function loadVarsFromStrings(array $ss): self
    {
        foreach ($ss as $kvStr) {
            if (strpos($kvStr, ':') > 0) {
                [$key, $value] = Str::explode($kvStr, ':', 2);
                // set tpl var
                $this->contexts[$key] = $value;
            }
        }

        return $this;
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
    public function addContexts(array $contexts): self
    {
        foreach ($contexts as $name => $value) {
            if (!isset($this->contexts[$name])) {
                $this->contexts[$name] = $value;
            }
        }
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

    /**
     * @return bool
     */
    public function isPrepared(): bool
    {
        return $this->prepared;
    }

    /**
     * @return array
     */
    public function getContexts(): array
    {
        return $this->contexts;
    }
}
