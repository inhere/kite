<?php
/**
 * Created by PhpStorm.
 * User: inhere
 * Date: 2017-10-18
 * Time: 18:58
 */

namespace Inhere\PTool\Console\Group;

use Inhere\Console\Controller;
use Inhere\Console\IO\Input;
use Inhere\Console\IO\Output;
use Toolkit\Sys\Sys;
use function is_dir;

/**
 * Class GitGroup
 * - php:cs-fix   add tag and push to remote
 * - php:lint delete the tag on remote
 *
 */
class PhpGroup extends Controller
{
    protected static $name = 'php';
    protected static $description = 'Some useful tool commands for php development';

    protected static function commandAliases(): array
    {
        return [
            'csfix'  => 'csFix',
            'cs-fix' => 'csFix',
        ];
    }

    /**
     * run php-cs-fixer for an dir, and auto add git commit message
     *
     * @options
     *  --not-commit  Dont run `git add` and `git commit` commands
     *
     * @arguments
     *  directory  The directory for run php-cs-fixer
     *
     * @param Input  $input
     * @param Output $output
     */
    public function csFixCommand(Input $input, Output $output): void
    {
        $dir = $input->getRequiredArg(0);

        if (!is_dir($dir)) {
            $output->error('please input an exists directory. current: ' . $dir);
            return;
        }

        [$code, $outMsg,] = Sys::run("php-cs-fixer fix $dir");

        echo $outMsg, "\n";

        if ($code === 0) {
            $gitCommand = "git add . && git commit -m \"up: format codes by run php-cs-fixer for $dir\"";
            $output->colored('> ' . $gitCommand, 'comment');

            [$code1, $outMsg,] = Sys::run($gitCommand);

            echo $outMsg, "\n";

            if ($code1 === 0) {
                $output->success('OK');
            }
        }
    }

    /**
     * run a php built-in server for development(is alias of the command 'server:dev')
     *
     * @usage
     *  {command} [-S HOST:PORT]
     *  {command} [-H HOST] [-p PORT]
     * @options
     *  -S         The server address. e.g 127.0.0.1:5577
     *  -H,--host  The server host address. e.g 127.0.0.1
     *  -p,--port  The server host address. e.g 5577
     */
    public function sync2Command()
    {
        echo "string\n";
    }
}
