<?php

use Inhere\Console\Application;
use Inhere\Console\IO\Input;
use Inhere\Kite\Console\CliApplication;
use Inhere\Kite\Console\Plugin\AbstractPlugin;

/**
 * Class DemoPlugin
 */
class DemoPlugin extends AbstractPlugin
{
    public function metadata(): array
    {
        return [
            'desc' => 'this is am demo plugin',
        ];
    }

    /**
     * @param Application $app
     * @param Input          $input
     */
    public function exec(Application $app, Input $input): void
    {
        vdump(__METHOD__);
    }
}
