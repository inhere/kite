<?php declare(strict_types=1);
/**
 * This file is part of PTool.
 *
 * @link     https://github.com/inhere
 * @author   https://github.com/inhere
 * @license  MIT
 */

namespace Inhere\PTool\Console\Command;

use Inhere\Console\Command;
use Inhere\Console\IO\Input;
use Inhere\Console\IO\Output;
use function array_keys;

/**
 * Class DemoCommand
 */
class DocCommand extends Command
{
    protected static $name = 'doc';

    protected static $description = 'Provide some useful docs for git,linux commands';

    public static function aliases(): array
    {
        return ['man', 'docs'];
    }

    /**
     * do execute
     *
     * @arguments
     *  top  The keywords
     *  sub  The sub keywords
     *
     * @param Input  $input
     * @param Output $output
     *
     * @example
     *  {fullCmd} git tag
     *  {fullCmd} git branch
     */
    protected function execute($input, $output)
    {
        $top = $input->getRequiredArg(0);
        $docs = $this->app->getParam('cmdDocs');

        if (!isset($docs[$top])) {
            $output->aList(array_keys($docs), "not found docs for the '{$top}', allow");
            return;
        }

        $map = $docs[$top];
        $sub = $input->getArg(1, '');
        if (!$sub) {
            $output->aList(array_keys($map), "please input sub key for '{$top}', allow");
            // $output->info("not found docs for the '{$sub}' in '{$top}'");
            return;
        }

        if (!isset($map[$sub])) {
            $output->aList(array_keys($map), "invalid sub key for '{$top}', allow");
            return;
        }

        $doc = $map[$sub];

        $output->title("Doc for the '{$top} {$sub}'", [
            'ucWords' => false,
        ]);
        $output->write($doc);
    }
}
