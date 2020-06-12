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

    protected static $description = 'quick generate new class or file from template';

    public static function aliases(): array
    {
        return ['generate'];
    }

    /**
     * @param Input  $input
     * @param Output $output
     */
    public function readmeCommand(Input $input, Output $output): void
    {
        $output->success('Complete');
    }
}
