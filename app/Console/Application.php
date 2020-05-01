<?php

namespace Inhere\PTool\Console;

/**
 *
 */
class Application extends \Inhere\Console\Application
{
    protected function prepareRun(): void
    {
        parent::prepareRun();

        date_default_timezone_set('PRC');
    }
}
