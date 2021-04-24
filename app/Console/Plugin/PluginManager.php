<?php declare(strict_types=1);

namespace Inhere\Kite\Console\Plugin;

use Inhere\Console\Util\Helper;
use Inhere\Kite\Console\Application;
use RuntimeException;
use SplFileInfo;
use Toolkit\Stdlib\Str;
use function basename;
use function class_exists;
use function is_dir;
use function is_file;
use function strlen;
use function strpos;
use function substr;
use function trim;

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
    private $loaded = false;

    /**
     * loaded plugin objects
     *
     * @var AbstractPlugin[]
     */
    private $plugins = [];

    /**
     * @var array
     */
    private $pluginDirs;

    /**
     * @var array
     */
    private $pluginFiles = [];

    /**
     * @var array
     */
    // private $classes = [];

    /**
     * @param array $pluginDirs
     *
     * @return static
     */
    public static function new(array $pluginDirs = []): self
    {
        return new self($pluginDirs);
    }

    /**
     * Class constructor.
     *
     * @param array $pluginDirs
     */
    public function __construct(array $pluginDirs = [])
    {
        $this->pluginDirs = $pluginDirs;
    }

    /**
     * @param string      $name plugin name or file path
     * @param Application $app
     */
    public function run(string $name, Application $app): void
    {
        $plugin = $this->getPlugin($name);
        if (!$plugin) {
            throw new RuntimeException('the plugin is not exists. plugin: ' . $name);
        }

        $plugin->run($app);
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

        if (!$name || strpos($name, ' ') !== false) {
            return false;
        }

        return $this->loadPlugin($name) ? true : false;
    }

    /**
     * check and load plugin
     *
     * @param string $name plugin name or file path
     *
     * @return bool
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
        if (Str::has($name, '.php') && is_file($name)) {
            // $path = $name;
            // $name = substr($name, 0, -4);

            $this->pluginFiles[$name] = $name;
            return true;
        }

        // find in all plugin dirs
        $founded = false;
        foreach ($this->pluginDirs as $dir) {
            $filename = $dir . '/' . $name . '.php';
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
            if (!is_dir($pluginDir)) {
                throw new RuntimeException("plugin dir: $pluginDir - is not exists");
            }

            $strLen   = strlen($pluginDir);
            $iterator = Helper::directoryIterator($pluginDir, $fileFilter);

            foreach ($iterator as $fi) {
                $filepath   = $fi->getPathname();
                $pluginName = substr($filepath, $strLen, -4);

                $this->pluginFiles[$pluginName] = $filepath;
            }
        }

        return $this;
    }

    /**
     * @return Closure
     */
    protected function getFileFilter(): callable
    {
        return static function (SplFileInfo $f) {
            $name = $f->getFilename();

            // Skip hidden files and directories.
            if (strpos($name, '.') === 0) {
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
        /** @noinspection PhpIncludeInspection */
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
}
