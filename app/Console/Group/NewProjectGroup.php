<?php declare(strict_types=1);

namespace Inhere\Kite\Console\Group;

use Inhere\Console\Controller;
use Inhere\Console\IO\Input;
use Inhere\Console\IO\Output;

/**
 * Class NewProjectGroup
 *
 * @package Inhere\Kite\Console\Group
 */
class NewProjectGroup extends Controller
{
    protected static $name = 'new';

    protected static $description = 'quick create new project or package or library tool commands';

    public static function aliases(): array
    {
        return ['create'];
    }

    protected static function commandAliases(): array
    {
        return [
            'project' => [
                'pro', 'app', 'application'
            ],
            'package' => [
                'pkg', 'lib', 'library'
            ],
        ];
    }

    public function projectCommand(Input $input, Output $output): void
    {
        $output->success('Complete');
    }

    public function packageCommand(Input $input, Output $output): void
    {
        $output->success('Complete');
    }
}
