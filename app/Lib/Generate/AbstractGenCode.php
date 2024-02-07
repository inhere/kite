<?php declare(strict_types=1);

namespace Inhere\Kite\Lib\Generate;

use Inhere\Kite\Helper\KiteUtil;
use Inhere\Kite\Lib\Defines\ClassMeta;
use Inhere\Kite\Lib\Defines\DataField\JsonField;
use Inhere\Kite\Lib\Defines\FieldMeta;
use Inhere\Kite\Lib\Defines\ProgramLang;
use InvalidArgumentException;
use PhpPkg\EasyTpl\EasyTemplate;
use Toolkit\FsUtil\File;
use Toolkit\Stdlib\Helper\Assert;
use Toolkit\Stdlib\Obj;
use Toolkit\Stdlib\OS;
use Toolkit\Stdlib\Str;
use function array_merge;
use function date;
use function dirname;
use function is_file;
use function strpos;

/**
 * class AbstractGenCode - abstract code generator
 *
 * @author inhere
 */
abstract class AbstractGenCode
{
    use Obj\Traits\QuickInitTrait;

    /**
     * @var bool
     */
    protected bool $prepared = false;

    /**
     * @var EasyTemplate|null template engine
     */
    private ?EasyTemplate $tplEng = null;

    /**
     * @var string
     */
    public string $tplDir = '';

    /**
     * @var string template file name or path.
     */
    public string $tplFile = '';

    /**
     * @var callable
     */
    protected $pathResolver;

    /**
     * @var string target language for generate.
     */
    protected string $lang = ProgramLang::PHP;

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
     * fields metadata list
     *
     * @var array<FieldMeta>
     */
    protected array $fields = [];

    /**
     * Sub objects/classes.
     *
     * ### Structure
     *
     * ```json
     * {
     *     'className1' => {
     *             'fieldName' => FieldMeta,
     *      },
     * }
     * ```
     *
     * @var array<string, array<string, FieldMeta>>
     */
    protected array $subObjects = [];


    public static function fromClassMeta(ClassMeta $meta, array $config = []): self
    {
        $self = new static($config);

        return $self->loadClassMeta($meta);
    }

    /**
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        if ($config) {
            $this->configThis($config);
        }
    }

    /**
     * @param ClassMeta $meta
     *
     * @return $this
     */
    public function loadClassMeta(ClassMeta $meta): static
    {
        Assert::isFalse($this->prepared, 'this object has prepared.');
        $this->prepared  = true;
        $this->className = $meta->name;

        $this->fields = $meta->fields;
        foreach ($meta->children as $child) {
            $this->subObjects[$child->name] = $child->fields;
        }

        return $this;
    }

    /**
     * @param array $meta array structure please refer the {@see ClassMeta}
     *
     * @return $this
     */
    public function loadArrayMeta(array $meta): static
    {
        return $this->loadClassMeta(ClassMeta::new($meta));
    }

    protected function prepareContext(): void
    {
        // defaults
        $this->addContexts([
            'className' => $this->className,
            'package'   => 'YOUR.PKG.NAME',
            'modulePkg' => 'MODULE_PKG',
            'pkgName'   => 'PKG_NAME',
            'subPkg'    => 'SUB_PKG',
            // common info
            'user'      => OS::getUserName(),
            'date'      => date('Y-m-d'),
            'datetime'  => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * @return static
     */
    public function prepare(): self
    {
        if ($this->prepared) {
            return $this;
        }

        $this->prepareContext();
        $this->prepared = true;

        return $this;
    }

    /**
     * @return EasyTemplate
     */
    public function getTplEng(): EasyTemplate
    {
        if (!$this->tplEng) {
            $this->tplEng = KiteUtil::newTplEngine([
                'tplDir' => $this->tplDir,
            ]);
        }

        return $this->tplEng;
    }

    /**
     * @return string
     */
    public function generate(): string
    {
        $this->prepare();

        $tplFile  = $this->findTplFile();
        $contexts = array_merge([
            'lang' => $this->getLang(),
            'user' => OS::getUserName(),
            'date' => date('Y-m-d'),
        ], $this->contexts);

        $contexts['fields'] = $this->fields;

        return $this->getTplEng()->renderFile($tplFile, $contexts);
    }

    public function renderSubObjects(): array
    {
        $strMap  = [];
        $tplFile = $this->findTplFile();

        if ($this->subObjects) {
            $ctx['withHead']  = false;
            $ctx['classSfx']  = '';
            $ctx['classMark'] = 'static ';

            foreach ($this->subObjects as $name => $fields) {
                $ctx['mainName']  = $name;
                $ctx['className'] = ucfirst($name);
                $ctx['fields']    = $fields;

                $strMap[$name] = $this->getTplEng()->renderFile($tplFile, $ctx);
            }
        }

        return $strMap;
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
    protected function findTplFile(): string
    {
        $tplFile = $this->tplFile;
        if (!$tplFile) {
            throw new InvalidArgumentException('Generate: template file is empty');
        }

        $tplFile = $this->resolvePath($tplFile);

        // check from tplDir
        if ($this->tplDir && !is_file($tplFile)) {
            $tplDir  = $this->resolvePath($this->tplDir);
            $dirFile = File::joinPath($tplDir, $tplFile);

            if (!is_file($dirFile)) {
                throw new InvalidArgumentException("No such template file: $tplFile");
            }

            $tplFile = $dirFile;
        }

        $this->tplFile = $tplFile;
        return $tplFile;
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
     * @param mixed  $value
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
     * @return static
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
     * @return static
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
     * @return static
     */
    public function setPathResolver(callable $pathResolver): self
    {
        $this->pathResolver = $pathResolver;
        return $this;
    }

    /**
     * @param string $className
     *
     * @return static
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

    public function getLang(): string
    {
        return $this->lang;
    }

    /**
     * @param string $lang
     *
     * @return $this
     */
    public function setLang(string $lang): static
    {
        $this->lang = $lang;
        return $this;
    }

    public function setSubObjects(array $subObjects): void
    {
        $this->subObjects = $subObjects;
    }
}
