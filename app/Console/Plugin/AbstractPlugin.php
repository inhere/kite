<?php declare(strict_types=1);

namespace Inhere\Kite\Console\Plugin;

use Inhere\Console\Application;
use Inhere\Console\GlobalOption;
use Inhere\Console\IO\Input;
use Toolkit\PFlag\Flags;
use Toolkit\PFlag\FlagsParser;
use Toolkit\PFlag\SFlags;
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
    protected $fs;

    /**
     * @var string
     */
    protected $name = '';

    /**
     * @var string
     */
    protected $filepath = '';

    /**
     * @var string
     */
    protected $classname = '';

    /**
     * @var array
     */
    protected $metadata = [];

    public function init(): void
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
        $fs = $this->createFlags();

        $fs->setDesc($this->metadata['desc']);
        $fs->setMoreHelp($this->metadata['help']);
        $fs->setExample($this->metadata['example']);
        $fs->addOptsByRules(GlobalOption::getCommonOptions());

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
    protected function createFlags(): FlagsParser
    {
        if (!$this->fs) {
            $this->fs = new Flags();
        }

        return $this->fs;
    }

    /**
     * @return SFlags
     */
    // protected function createSFlags(): SFlags
    // {
    //     $this->fs = new SFlags();
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
    public function getOptions(): array
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
            // 'desc'     => $meta['desc'] ?? '',
            'class'  => $this->classname,
            'author' => $this->metadata['author'],
            'version' => $this->metadata['version'],
            'path'   => $this->filepath,
        ];
    }

    /**
     * @param Application $app
     * @param array $args
     */
    public function run(Application $app, array $args = []): void
    {
        $input = $app->getInput();
        if (!$this->beforeRun($app, $args)) {
            return;
        }

        $this->exec($app, $input);
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
            $app->debugf('parse plugin flags, args: %s', DataHelper::toString($args));
            $ok = $this->fs->parse($args);

            if (!$ok) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param Application $app
     * @param Input $input
     */
    abstract public function exec(Application $app, Input $input): void;

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
