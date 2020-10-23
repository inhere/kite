<?php declare(strict_types=1);
/**
 * This file is part of Kite.
 *
 * @link     https://github.com/inhere
 * @author   https://github.com/inhere
 * @license  MIT
 */

namespace Inhere\Kite\Console\Command;

use Inhere\Console\Command;
use Inhere\Console\Exception\PromptException;
use Inhere\Console\IO\Input;
use Inhere\Console\IO\Output;
use Toolkit\Sys\Sys;
use const PHP_VERSION;

/**
 * Class WebCommand
 */
class WebCommand extends Command
{
    protected static $name = 'web';

    protected static $description = 'start an web application serve';

    public static function aliases(): array
    {
        return ['serve'];
    }

    /**
     * Configure command
     */
    protected function configure(): void
    {
        $def = $this->createDefinition();

        $def->addArg('entryFile', Input::ARG_OPTIONAL, 'The entry file for server. default: public/index.php');
    }

    /**
     * @param  Input $input
     * @param  Output $output
     * @example testing
     */
    protected function execute($input, $output)
    {
        $conf = $this->app->getParam('webServe', []);
        if (!$conf) {
            throw new PromptException('please config the "webServe" settings');
        }

        $host = $conf['host'] ?? '127.0.0.1:8552';
        $root = $conf['root'] ?? 'public';

        $version = PHP_VERSION;
        $this->write([
            "PHP $version Development Server started\nServer listening on http://<info>$host</info>",
            "Document root is <comment>$root</comment>",
            'You can use <comment>CTRL + C</comment> to stop run.',
        ]);

        // $command = "php -S {$server} -t web web/index.php";
        $command = "php -S {$host}";

        if ($root) {
            $command .= " -t $root";
        }

        if ($entryFile = $input->getStringArg('entryFile')) {
            $command .= " $entryFile";
        }

        $this->write("<comment>></comment> <info>$command</info>");

        Sys::execute($command);
    }
}
