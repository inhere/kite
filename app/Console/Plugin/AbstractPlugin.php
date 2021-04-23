<?php declare(strict_types=1);

namespace Inhere\Kite\Console\Plugin;

use Inhere\Kite\Console\Application;

/**
 * Class AbstractPlugin
 *
 * @package Inhere\Kite\Plugin
 */
abstract class AbstractPlugin
{
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
     * @return array
     */
    public function metadata(): array
    {
        return [
            // 'author' => 'inhere',
            // 'version' => '',
            // 'desc' => '',
        ];
    }

    /**
     * @return array[]
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
     * @param Application $app
     */
    public function run(Application $app): void
    {
        $this->exec($app);
    }

    /**
     * @param Application $app
     */
    abstract public function exec(Application $app): void;

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
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
}
