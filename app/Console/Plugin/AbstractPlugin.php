<?php declare(strict_types=1);

namespace Inhere\Kite\Console\Plugin;

use Inhere\Console\Application;
use Inhere\Console\GlobalOption;
use Inhere\Console\IO\Output;
use PhpPkg\EasyTpl\SimpleTemplate;
use Toolkit\PFlag\Flags;
use Toolkit\PFlag\FlagsParser;
use Toolkit\Stdlib\Helper\DataHelper;
use function array_merge;

/**
 * Class AbstractPlugin
 *
 * @package Inhere\Kite\Plugin
 */
abstract class AbstractPlugin implements PluginInterface
{
    /**
     * @var FlagsParser
     */
    protected FlagsParser $fs;

    /**
     * @var string
     */
    protected string $name = '';

    /**
     * @var string
     */
    protected string $filepath = '';

    /**
     * @var string
     */
    protected string $classname = '';

    /**
     * @var array
     */
    protected array $metadata = [];

    public function __construct()
    {
        $this->fs = new Flags();
        // $this->createFlags();
    }

    public function initObj(): void
    {
        $this->metadata = array_merge([
            'author'  => 'inhere',
            'version' => 'v0.0.1',
            'desc'    => '',
            'help'    => '',
            'example' => '',
        ], $this->metadata());

        $this->createAndInitFlags();
    }

    protected function createAndInitFlags(): void
    {
        $fs = $this->fs;

        $fs->setDesc($this->metadata['desc']);
        $fs->setMoreHelp($this->metadata['help']);
        $fs->setExample($this->metadata['example']);
        $fs->addOptsByRules(GlobalOption::getCommonOptions());

        // replace help tpl vars
        $fs->setBeforePrintHelp(function (string $help): string {
            return $this->applyHelpVars($help);
        });

        $loaded = false;
        if ($optRules = $this->options()) {
            $loaded = true;
            $fs->addOptsByRules($optRules);
        }

        if ($argRules = $this->arguments()) {
            $loaded = true;
            $fs->addArgsByRules($argRules);
        }

        if ($loaded) {
            $fs->lock();
        }
    }

    /**
     * @return FlagsParser
     */
    // protected function createFlags(): FlagsParser
    // {
    //     if (!$this->fs) {
    //         $this->fs = new Flags();
    //     }
    //
    //     return $this->fs;
    // }

    /**
     * Metadata for the plugin
     *
     * @return array
     */
    protected function metadata(): array
    {
        return [
            // 'author'  => 'inhere',
            // 'version' => '',
            // 'desc'    => '',
            // 'help'    => string|array,
            // 'example' => 'string|array',
        ];
    }

    /**
     * flag options for the plugin
     *
     * @return array
     */
    protected function options(): array
    {
        return [
            // 'file' => 'string;the Idea Http Request file',
        ];
    }

    /**
     * flag arguments for the plugin
     *
     * @return array
     */
    protected function arguments(): array
    {
        return [
            // 'file' => 'the Idea Http Request file',
        ];
    }

    /**
     * @return array
     */
    protected function helpTplVars(): array
    {
        return [
            'plugName' => $this->name,
        ];
    }

    /**
     * @return array
     */
    public function getInfo(): array
    {
        return [
            'class'    => $this->classname,
            'name'     => $this->name,
            'path'     => $this->filepath,
            'metadata' => $this->metadata(),
        ];
    }

    /**
     * @return array
     */
    final public function getOptions(): array
    {
        return $this->options();
    }

    /**
     * @return array
     */
    public function getSimpleInfo(): array
    {
        // $meta = $this->metadata();
        return [
            'name'   => $this->name,
            'version' => $this->metadata['version'],
            'author' => $this->metadata['author'],
            // 'desc'     => $meta['desc'] ?? '',
            'class'  => $this->classname,
            'path'   => $this->filepath,
        ];
    }

    /**
     * @param Application $app
     * @param array $args
     *
     * @return bool
     */
    protected function beforeRun(Application $app, array $args): bool
    {
        if ($this->fs->isNotEmpty()) {
            $app->debugf('parse plugin %s flags, args: %s', $this->name, DataHelper::toString($args));
            $ok = $this->fs->parse($args);

            if (!$ok) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param Application $app
     * @param array $args
     */
    public function run(Application $app, array $args = []): void
    {
        if (!$this->beforeRun($app, $args)) {
            return;
        }

        $this->exec($app, $app->getOutput());
    }

    /**
     * @param Application $app
     * @param Output $output
     */
    abstract public function exec(Application $app, Output $output): void;

    /**
     * replace help tpl vars
     *
     * @param string $help
     *
     * @return string
     */
    protected function applyHelpVars(string $help): string
    {
        return SimpleTemplate::new(['format' => '${%s}'])->renderString($help, $this->helpTplVars());
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getDesc(): string
    {
        return $this->metadata['desc'] ?? 'no description';
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @param string $filepath
     */
    public function setFilepath(string $filepath): void
    {
        $this->filepath = $filepath;
    }

    /**
     * @param string $classname
     */
    public function setClassname(string $classname): void
    {
        $this->classname = $classname;
    }

    /**
     * @return array
     */
    public function getMetadata(): array
    {
        return $this->metadata;
    }
}
