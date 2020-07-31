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
use Inhere\Kite\Common\CmdRunner;

/**
 * Class GoController
 * - go:fmt   format go codes by go fmt
 * - go:lint  run go lint check
 *
 */
class GoController extends Controller
{
    protected static $name = 'go';

    protected static $description = 'Some useful tool commands for go development';

    protected static function commandAliases(): array
    {
        return [
            'fmt'    => 'format',
            'search' => 'pkgSearch',
            'pkgUp'     => [
                'up', 'pkgup'
            ],
        ];
    }

    /**
     * run go fmt for current directory
     *
     * @options
     *  --not-commit  Dont run `git add` and `git commit` commands
     *
     * @arguments
     *  directory  The directory for run go fmt
     *
     * @param Input  $input
     * @param Output $output
     *
     * @example
     *  {binWithCmd} src/rpc-client
     */
    public function formatCommand(Input $input, Output $output): void
    {
        CmdRunner::new('go fmt ./...')->do(true);

        $output->success('OK');
    }

    /**
     * Search php package from packagist.org
     *
     * @param Input  $input
     * @param Output $output
     */
    public function pkgSearch(Input $input, Output $output): void
    {
        $output->success('TODO');
    }

    /**
     * List all packages from of the project. from go.mod
     *
     * @param Input  $input
     * @param Output $output
     */
    public function pkgListCommand(Input $input, Output $output): void
    {
        $filepath = $input->getWorkDir() . '/go.mod';
        $content = \file_get_contents($filepath);

        echo $content, "\n";

        $output->success('OK');
    }

    /**
     * @param Input $input
     */
    protected function pkgUpConfigure(Input $input): void
    {
        $input->bindArguments(['pkgName' => 0]);
    }

    /**
     * update the package to latest by `go get -u`
     *
     * @arguments
     *  pkgName     The package name. eg: gookit/rux
     *
     * @param Input  $input
     * @param Output $output
     *
     * @example
     *  {binWithCmd} gookit/rux
     */
    public function pkgUpCommand(Input $input, Output $output): void
    {
        $pkgName = $input->getRequiredArg('pkgName');
        $pkgPath = "github.com/$pkgName";

        $output->aList([
            'pkgName' => $pkgName,
            'pkgPath' => $pkgPath,
        ], 'information', ['ucFirst' => false]);

        CmdRunner::new('go get -u ' . $pkgPath)->do(true);
    }
}
