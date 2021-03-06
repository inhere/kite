<?php declare(strict_types=1);

namespace Inhere\Kite\Console\Controller;

use Inhere\Console\Controller;
use Inhere\Console\IO\Input;
use Inhere\Console\IO\Output;

/**
 * Class NewProjectGroup
 *
 * @package Inhere\Kite\Console\Group
 */
class ProjectController extends Controller
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
                'pkg', 'lib', 'cpt', 'library'
            ],
        ];
    }

    public function projectCommand(Input $input, Output $output): void
    {
        $output->success('Complete');
    }

    /**
     * @param Input  $input
     * @param Output $output
     */
    public function packageCommand(Input $input, Output $output): void
    {


        $output->success('Complete');
    }
}
