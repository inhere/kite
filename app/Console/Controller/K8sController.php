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
use Inhere\Console\Util\Show;
use function basename;
use function glob;
use function preg_match;
use const GLOB_MARK;

/**
 * Class K8sController
 */
class K8sController extends Controller
{
    protected static $name = 'k8s';

    protected static $description = 'Kubernetes development tool commands';

    /**
     * generate apply template contents for k8s
     *
     * @options
     *  -s, --source    The source sql file
     *  -o, --output    The output content file
     *
     * @param Input  $input
     * @param Output $output
     *
     */
    public function genTplCommand(Input $input, Output $output): void
    {
        $output->info('WIP');
    }
}
