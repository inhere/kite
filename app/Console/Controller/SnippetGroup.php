<?php declare(strict_types=1);
/**
 * This file is part of Kite.
 *
 * @link     https://github.com/inhere
 * @author   https://github.com/inhere
 * @license  MIT
 */

namespace Inhere\Kite\Console\Controller;

use Inhere\Console\Controller;
use Inhere\Console\IO\Input;
use Inhere\Console\IO\Output;

/**
 * Class SnippetGroup
 */
class SnippetGroup extends Controller
{
    protected static $name = 'snippet';

    protected static $description = 'Some useful development tool commands';

    /**
     * @return string[]
     */
    public static function aliases(): array
    {
        return ['snippets', 'snip'];
    }

    /**
     * list all code snippets
     *
     * @param Input  $input
     * @param Output $output
     */
    public function listCommand(Input $input, Output $output): void
    {
        echo "string\n";
    }
}
