<?php declare(strict_types=1);

namespace Inhere\Kite\Console\Group;

use Inhere\Console\Controller;
use Inhere\Console\IO\Input;
use Inhere\Console\IO\Output;

/**
 * Class GenerateGroup
 *
 * @package Inhere\Kite\Console\Group
 */
class GenerateGroup extends Controller
{
    protected static $name = 'gen';

    protected static $description = 'quick create new project or package or library tool commands';

    public static function aliases(): array
    {
        return ['generate'];
    }

    public function serveCommand(Input $input, Output $output): void
    {
        $output->success('Complete');
    }
}
