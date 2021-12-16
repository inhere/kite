<?php declare(strict_types=1);

namespace Inhere\Kite\Console\Plugin;

use Inhere\Console\Application;
use Inhere\Console\Util\Show;
use RuntimeException;
use SplFileInfo;
use Toolkit\Cli\Cli;
use Toolkit\Cli\Color;
use Toolkit\FsUtil\Dir;
use Toolkit\Stdlib\Obj;
use Toolkit\Stdlib\Str;
use function array_keys;
use function array_search;
use function basename;
use function class_exists;
use function count;
use function implode;
use function is_dir;
use function is_file;
use function key;
use function rtrim;
use function str_contains;
use function strlen;
use function strpos;
use function strtolower;
use function substr;
use function trim;
use function vdump;

/**
 * Class PluginManager
 *
 * @package Inhere\Kite\Console\Plugin
 */
class PluginManager
{
    /**
     * @var bool
     */
    private bool $loaded = false;

    /**
     * @var bool
     */
    private bool $enable = true;

    /**
     * loaded plugin objects
     *
     * @var AbstractPlugin[]
     */
    private array $plugins = [];

    /**
     * @var array
     */
    private array $pluginDirs = [];

    /**
     * @var array<string, string>
     */
    private array $pluginFiles = [];

    /**
     * @var array
     */
    // private $classes = [];

    /**
     * @param array $config
     *
     * @return static
     */
    public static function new(array $config = []): self
    {
        return new self($config);
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
     * @param string $name plugin name or file path
     * @param Application $app
     * @param array $args
     */
    public function run(string $name, Application $app, array $args = []): void
    {
        $plugin = $this->getPlugin($name);
        if (!$plugin) {
            $this->loadPluginFiles();

            $matched = $words = [];
            // input is multi words for match.
            if (str_contains($name, ' ')) {
                $words = Str::explode($name, ' ');
            }

            foreach ($this->pluginFiles as $plugName => $pluginFile) {
                if ($words && Str::iHasAll($plugName, $words)) {
                    $matched[$plugName] = $pluginFile;
                } elseif (Str::ihas($plugName, $name)) {
                    $matched[$plugName] = $pluginFile;
                }
            }

            // if can match only one, run it.
            if (count($matched) === 1) {
                $pluginName = (string)key($matched);
                Cli::info("[Tips] auto match and run the plugin: $pluginName");

                $plugin = $this->getPlugin($pluginName);
            } else {
                if (count($matched) > 1) {
                    Cli::warn("[Tips] keywords can match multi plugins: " . implode(',', array_keys($matched)));
                }

                throw new RuntimeException("the plugin '$name' is not exists.");
            }
        }

        $plugin->run($app, $args);
    }

    /**
     * @param AbstractPlugin $plugin
     */
    public function showInfo(AbstractPlugin $plugin): void
    {
        Color::println($plugin->getName() . ':', 'comment');
        Color::println('  ' . $plugin->getDesc() . "\n", 'normal');

        // Show::aList($plugin->getHelpInfo(), 'Information');
        $panel = [
            'Information' => $plugin->getSimpleInfo(),
        ];

        // input options
        if ($plugOpts = $plugin->getOptions()) {
            $panel['plugin options:'] = $plugOpts;
        }

        $meta = $plugin->getMetadata();
        if ($meta['example']) {
            $panel['example'] = $meta['example'];
        }

        if ($meta['help']) {
            $panel['help'] = $meta['help'];
        }

        Show::mList($panel);
    }

    /**
     * @param string $name
     *
     * @return AbstractPlugin|null
     */
    public function getPlugin(string $name): ?AbstractPlugin
    {
        return $this->plugins[$name] ?? $this->loadPlugin($name);
    }

    /**
     * check plugin, and load plugin
     *
     * @param string $name plugin name or file path
     *
     * @return bool
     */
    public function isPlugin(string $name): bool
    {
        $name = trim($name);
        $name = trim($name, '/');

        if (!$name || str_contains($name, ' ')) {
            return false;
        }

        return $this->loadPlugin($name) !== null;
    }

    /**
     * check and load plugin
     *
     * @param string $name plugin name or file path
     *
     * @return AbstractPlugin|null
     */
    protected function loadPlugin(string $name): ?AbstractPlugin
    {
        // not found
        if (!isset($this->pluginFiles[$name]) && !$this->loadPluginFile($name)) {
            return null;
        }

        $filename = $this->pluginFiles[$name];
        $this->requireFile($filename);

        $className = strpos($name, '/') > 0 ? basename($name) : $name;
        if (Str::has($className, '.php')) {
            $className = substr($className, 0, -4);
        }

        $className = Str::camelCase($className, true);
        if (!class_exists($className, false)) {
            throw new RuntimeException('the plugin file is not an class, plugin: ' . $name);
        }

        $pluginObj = new $className;
        if (!($pluginObj instanceof AbstractPlugin)) {
            throw new RuntimeException('plugin class must extends: ' . AbstractPlugin::class);
        }

        $pluginObj->setName($name);
        $pluginObj->setFilepath($filename);
        $pluginObj->setClassname($className);
        $pluginObj->init();

        $this->plugins[$name] = $pluginObj;
        return $pluginObj;
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    protected function loadPluginFile(string $name): bool
    {
        // is an exists php file
        $hasPhpSuffix = Str::has($name, '.php');
        if ($hasPhpSuffix && is_file($name)) {
            // $path = $name;
            // $name = substr($name, 0, -4);
            $this->pluginFiles[$name] = $name;
            return true;
        }

        // `$name . '.php'` is an exists php file
        if (!$hasPhpSuffix && is_file($name . '.php')) {
            $this->pluginFiles[$name] = $name . '.php';
            return true;
        }

        // find in all plugin dirs
        $founded = false;
        foreach ($this->pluginDirs as $dir) {
            $filename = rtrim($dir, '/') . '/' . $name . '.php';
            if (is_file($filename)) {
                $founded = true;

                $this->pluginFiles[$name] = $filename;
                break;
            }
        }

        return $founded;
    }

    /**
     * load all plugin files
     */
    public function loadPluginFiles(): self
    {
        if ($this->loaded) {
            return $this;
        }

        $this->loaded = true;
        if (!$this->pluginDirs) {
            return $this;
        }

        $fileFilter = $this->getFileFilter();
        foreach ($this->pluginDirs as $pluginDir) {
            // support parse ~ as user home dir.
            $pluginDir = Dir::realpath($pluginDir);
            if (!is_dir($pluginDir)) {
                throw new RuntimeException("plugin dir: $pluginDir - is not exists");
            }

            $pathLen  = strlen($pluginDir) + 1;
            $iterator = Dir::getIterator($pluginDir, $fileFilter);

            foreach ($iterator as $fi) {
                $filepath   = $fi->getPathname();
                $pluginName = substr($filepath, $pathLen, -4);

                $this->pluginFiles[$pluginName] = $filepath;
            }
        }

        return $this;
    }

    /**
     * @return callable
     */
    protected function getFileFilter(): callable
    {
        return static function (SplFileInfo $f) {
            $name = $f->getFilename();

            // Skip hidden files and directories.
            if (str_starts_with($name, '.')) {
                return false;
            }

            // go on read sub-dir
            if ($f->isDir()) {
                return true;
            }

            // php file
            return $f->isFile() && substr($name, -4) === '.php';
        };
    }

    /**
     * @param string $phpFile
     */
    private function requireFile(string $phpFile): void
    {
        require_once $phpFile;
    }

    /**
     * @return array
     */
    public function getPluginDirs(): array
    {
        return $this->pluginDirs;
    }

    /**
     * @return array
     */
    public function getPluginFiles(): array
    {
        return $this->pluginFiles;
    }

    /**
     * @return bool
     */
    public function isEnable(): bool
    {
        return $this->enable;
    }

    /**
     * @param bool|int $enable
     */
    public function setEnable(bool|int $enable): void
    {
        $this->enable = (bool)$enable;
    }

    /**
     * @param array $pluginDirs
     */
    public function setPluginDirs(array $pluginDirs): void
    {
        $this->pluginDirs = $pluginDirs;
    }
}
