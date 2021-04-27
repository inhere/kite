<?php declare(strict_types=1);

namespace Inhere\Kite\Console\Plugin;

use Inhere\Console\IO\Input;
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
     * Metadata for the plugin
     *
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
     * options for the plugin
     *
     * @return array
     */
    public function options(): array
    {
        return [
            // 'file' => 'the Idea Http Request file',
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
     * @return array
     */
    public function getHelpInfo(): array
    {
        // $meta = $this->metadata();

        return [
            'name'     => $this->name,
            // 'desc'     => $meta['desc'] ?? '',
            'class'    => $this->classname,
            'path'     => $this->filepath,
        ];
    }

    /**
     * @param Application $app
     * @param Input       $input
     */
    public function run(Application $app, Input $input): void
    {
        // TODO before run
        $this->exec($app, $input);
    }

    /**
     * @param Application $app
     * @param Input       $input
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
        return $this->metadata()['desc'] ?? 'no description';
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
