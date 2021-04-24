<?php

use Inhere\Kite\Console\Application;
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

    public function exec(Application $app): void
    {
        vdump(__METHOD__);
    }
}
