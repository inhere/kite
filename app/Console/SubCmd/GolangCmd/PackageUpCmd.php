<?php declare(strict_types=1);

namespace Inhere\Kite\Console\SubCmd\GolangCmd;

use Inhere\Console\Command;
use Inhere\Console\IO\Input;
use Inhere\Console\IO\Output;
use Inhere\Kite\Common\CmdRunner;

/**
 * @author inhere
 */
class PackageUpCmd extends Command
{
    protected static string $name = 'pkg-up';
    protected static string $desc = 'update the package to latest by `go get -u`';

    protected function configure(): void
    {
        // $this->flags->addOptByRule($name, $rule);
    }

    /**
     * Do execute command
     *
     * @arguments
     *   pkgName     string;The package name. eg: gookit/rux;required
     *
     * @param Input  $input
     * @param Output $output
     *
     * @return int
     *
     * @example
     *   {binWithCmd} gookit/rux
     */
    protected function execute(Input $input, Output $output): int
    {
        $pkgName = $this->flags->getArg('pkgName');
        $pkgPath = "github.com/$pkgName";

        $output->aList([
            'pkgName' => $pkgName,
            'pkgPath' => $pkgPath,
        ], 'information', ['ucFirst' => false]);

        CmdRunner::new('go get -u ' . $pkgPath)->do(true);
        return 0;
    }
}