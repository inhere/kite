<?php declare(strict_types=1);

namespace Inhere\Kite\Lib\Generate;

use Inhere\Kite\Helper\KiteUtil;
use InvalidArgumentException;
use Toolkit\FsUtil\File;
use Toolkit\Stdlib\Obj;
use Toolkit\Stdlib\OS;
use function array_merge;
use function date;
use function dirname;
use function is_file;
use function trim;

/**
 * class AbstractJsonToCode
 */
abstract class AbstractJsonToCode
{
    /**
     * @var bool
     */
    private bool $prepared = false;

    /**
     * Source json(5) codes
     *
     * @var string
     */
    protected string $source = '';

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
     * @var array
     */
    // private array $jsonData = [];

    /**
     * @var array
     */
    private array $fields = [];

    /**
     * @return string
     */
    public function generate(): string
    {
        $this->prepare();

        return $this->renderTplText();
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

        $json = $this->source;
        if (!$json = trim($json)) {
            throw new InvalidArgumentException('empty source json(5) data for generate');
        }

        $jd = Json5Data::new()->loadFrom($json);

        $this->fields = $jd->getFields();
        $this->setContexts($jd->getSettings());

        return $this;
    }

    /**
     * @return string
     */
    protected function renderTplText(): string
    {
        $tplText = $this->readSourceFromFile();
        $tplEng  = KiteUtil::newTplEngine();

        $settings = array_merge([
            'lang' => 'java',
            'user' => OS::getUserName(),
            'date' => date('Y-m-d'),
        ], $this->contexts);

        $settings['fields'] = $this->fields;
        // $tplVars = [
        //     'ctx'    => $settings,
        //     'fields' => $this->fields,
        // ];

        return $tplEng->renderString($tplText, $settings);
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
     * @param string $source
     *
     * @return self
     */
    public function setSource(string $source): self
    {
        $this->source = $source;
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
     * @param callable $pathResolver
     *
     * @return AbstractJsonToCode
     */
    public function setPathResolver(callable $pathResolver): self
    {
        $this->pathResolver = $pathResolver;
        return $this;
    }
}
