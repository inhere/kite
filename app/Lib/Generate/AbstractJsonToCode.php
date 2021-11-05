<?php declare(strict_types=1);

namespace Inhere\Kite\Lib\Generate;

use ColinODell\Json5\Json5Decoder;
use Inhere\Kite\Helper\KiteUtil;
use Inhere\Kite\Lib\Generate\Java\JavaType;
use Inhere\Kite\Lib\Generate\Json\JsonField;
use Inhere\Kite\Lib\Parser\Text\Json5LineParser;
use Inhere\Kite\Lib\Parser\Text\TextParser;
use InvalidArgumentException;
use Throwable;
use Toolkit\FsUtil\File;
use Toolkit\Stdlib\Obj;
use Toolkit\Stdlib\OS;
use Toolkit\Stdlib\Str;
use Toolkit\Stdlib\Type;
use function array_merge;
use function dirname;
use function gettype;
use function is_file;
use function preg_match;
use function str_contains;
use function str_ends_with;
use function str_replace;
use function strpos;
use function substr;
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
     * @throws Throwable
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

        // auto add quote char
        if ($json[0] !== '{') {
            $json = '{' . $json . "\n}";
        }

        $comments = [];
        $jsonData = Json5Decoder::decode($json, true);

        // has comments chars
        if (str_contains($json, '//')) {
            $p = TextParser::newWithParser($json, new Json5LineParser())
                ->withConfig(function (TextParser $p) {
                    $p->headerSep = "\n//###\n";
                })
                ->setBeforeParseHeader(function (string $header) {
                    if ($pos = strpos($header, "//##\n")) {
                        $header = substr($header, $pos + 4);
                        $header = str_replace("\n//", '', $header);
                    }
                    return $header;
                })
                ->parse();

            $comments = $p->getStringMap('field', 'comment');
            $this->setContexts($p->getSettings());
        }

        foreach ($jsonData as $key => $value) {
            $this->fields[$key] = JsonField::new([
                'name' => $key,
                'type' => gettype($value),
                'desc' => $comments[$key] ?? $key,
            ]);
        }
        return $this;
    }

    /**
     * @return string
     * @throws Throwable
     */
    protected function renderTplText(): string
    {
        $tplText = $this->readSourceFromFile();
        $tplEng  = KiteUtil::newTplEngine($tplText);

        $tplEng->addFunction(
            'needToSnake',
            function ($paramArr) {
                $name = $paramArr['name'];
                if (str_contains($name, '_')) {
                    return true;
                }

                if (preg_match('/[A-Z]/', $name)) {
                    return true;
                }

                return false;
            });

        // {toJavaType type=field.type name=field.name}
        $tplEng->addFunction(
            'toJavaType',
            function ($paramArr, $command, $context, $cmdParam, $self) {
                $type = $paramArr['type'];
                $name = $paramArr['name'];
                if ($type === Type::ARRAY) {
                    return JavaType::OBJECT;
                }

                if (str_ends_with($name, 'id') || str_ends_with($name, 'Id')) {
                    return JavaType::LONG;
                }

                return Str::upFirst($type);
            });

        $settings = array_merge([
            'user' => OS::getUserName(),
        ], $this->contexts);

        $tplVars = [
            'ctx'    => $settings,
            'fields' => $this->fields,
        ];

        return $tplEng->apply($tplVars, false);
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
    public function configObj(array $config): self
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
            $tplFile = File::joinPath($tplDir, $tplFile);

            File::assertIsFile($tplFile);
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
