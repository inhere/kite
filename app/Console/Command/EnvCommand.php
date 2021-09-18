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
use Toolkit\Stdlib\OS;
use function is_scalar;

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
     *  --format              Format the env value
     *  --match-value         Match ENV value by keywords. default is match key.
     *  --split               Split the env value by given char. eg ':' ','
     *  -s, --search STRING   The keywords for search ENV information
     *
     * @arguments
     *  name STRING   The name in the ENV or keywords for search ENV keys
     *
     * @param  Input $input
     * @param  Output $output
     */
    protected function execute(Input $input, Output $output)
    {
        $keywords = $input->getSameStringOpt('s,search');

        $name = $input->getFirstArg();
        if (!$name && !$keywords) {
            // env | grep XXX
            $output->aList($_SERVER, 'ENV Information', ['ucFirst' => false]);
            return;
        }

        if ($name) {
            $value = null;
            $upKey = strtoupper($name);
            if (isset($_SERVER[$upKey])) {
                $value = $_SERVER[$upKey];
            } elseif (isset($_SERVER[$name])) {
                $value = $_SERVER[$name];
            }

            if ($value !== null) {
                $sepChar = $input->getStringOpt('split');
                if ($upKey === 'PATH') {
                    $sepChar = OS::isWin() ? ';' : ':';
                }

                if ($sepChar) {
                    $output->aList(explode($sepChar, $value), "$upKey value:", ['ucFirst' => false]);
                } else {
                    $output->println("$upKey value:");
                    $output->colored($value);
                }
                return;
            }
        }

        $keywords = $keywords ?: $name;
        $matchVal = $input->getBoolOpt('match-value');
        $matched  = $this->searchSERVER($keywords, $matchVal);

        if (!$matched) {
            $matched = ['NOT MATCHED'];
        }

        $output->aList($matched, "Matched Results(kw:{$keywords})", ['ucFirst' => false]);
    }

    /**
     * @param string $keywords
     * @param boolean $matchVal
     * @return array
     */
    private function searchSERVER(string $keywords, bool $matchVal): array
    {
        $matched  = [];
        foreach ($_SERVER as $key => $value) {
            $hayStack = $matchVal ? $value : $key;
            if (!is_scalar($hayStack)) {
                continue;
            }

            if (stripos((string)$hayStack, $keywords) !== false) {
                $matched[$key] = $value;
            }
        }

        return $matched;
    }
}
