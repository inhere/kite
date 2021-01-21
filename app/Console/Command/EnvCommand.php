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
use Inhere\Console\IO\Input;
use Inhere\Console\IO\Output;
use function is_scalar;
use function strpos;

/**
 * Class DemoCommand
 */
class EnvCommand extends Command
{
    protected static $name = 'env';

    protected static $description = 'a test command';

    /**
     * print system ENV information
     *
     * @options
     *  --format        Format the env value
     *  --split         Split the env value by given char. eg ':' ','
     *  --match-value   Match ENV value by keywords. default is match key.
     *
     * @arguments
     *  keywords    The keywords for search ENV
     *
     * @param  Input $input
     * @param  Output $output
     */
    protected function execute($input, $output)
    {
        $keywords = $input->getFirstArg();
        if (!$keywords) {
            // env | grep XXX
            $output->aList($_SERVER, 'ENV Information', ['ucFirst' => false]);
            return;
        }

        $value = null;
        $upKey = strtoupper($keywords);
        if (isset($_SERVER[$upKey])) {
            $value = $_SERVER[$upKey];
        } elseif (isset($_SERVER[$keywords])) {
            $value = $_SERVER[$keywords];
        }

        if ($value !== null) {
            $sepChar = $input->getStringOpt('split');
            if ($upKey === 'PATH') {
                $sepChar = ':';
            }

            if ($sepChar) {
                $output->aList(explode($sepChar, $value), "$upKey value", ['ucFirst' => false]);
            } else {
                $output->colored($value);
            }
            return;
        }

        $matched  = [];
        $matchVal = $input->getBoolOpt('match-value');

        foreach ($_SERVER as $key => $value) {
            $hayStack = $matchVal ? $value : $key;
            if (!is_scalar($hayStack)) {
                continue;
            }

            if (stripos((string)$hayStack, $keywords) !== false) {
                $matched[$key] = $value;
            }
        }

        if (!$matched) {
            $matched = ['NOT MATCHED'];
        }

        $output->aList($matched, "Matched Results(kw:{$keywords})", ['ucFirst' => false]);
    }
}
