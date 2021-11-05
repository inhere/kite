<?php

use Inhere\Console\Application;
use Inhere\Console\IO\Output;
use Inhere\Kite\Console\CliApplication;
use Inhere\Kite\Console\Plugin\AbstractPlugin;

/**
 * Class DemoPlugin
 */
class DemoPlugin extends AbstractPlugin
{
    protected function metadata(): array
    {
        return [
            'desc' => 'this is am demo plugin',
        ];
    }

    /**
     * @param Application $app
     * @param Output          $output
     */
    public function exec(Application $app, Output $output): void
    {
        vdump(__METHOD__);
    }
}
