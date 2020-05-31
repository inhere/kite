<?php declare(strict_types=1);
/**
 * This file is part of Kite.
 *
 * @link     https://github.com/inhere
 * @author   https://github.com/inhere
 * @license  MIT
 */

namespace Inhere\Kite\Console\Group;

use Inhere\Console\Controller;

/**
 * Class FileGroup
 */
class FileGroup extends Controller
{
    protected static $name = 'file';

    protected static $description = 'Some useful development tool commands';

    /**
     * create ln
     *
     * @options
     *  -s, --src  The server address. e.g 127.0.0.1:5577
     *  -d, --dst  The server host address. e.g 127.0.0.1
     *
     */
    public function lnCommand(): void
    {
        // ln -s "$PWD"/bin/htu /usr/local/bin/htu

        echo "string\n";
    }
}
