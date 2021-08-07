<?php declare(strict_types=1);

namespace Inhere\Kite\Common\Log;

use Inhere\Kite\Kite;
use Monolog\Processor\ProcessorInterface;

/**
 * Class CliLogProcessor
 *
 * @package Inhere\Kite\Common\Log
 */
class CliLogProcessor implements ProcessorInterface
{
    /**
     * @return array The processed record
     *
     * @phpstan-param  Record $record
     * @phpstan-return Record
     */
    public function __invoke(array $record)
    {
        $workDir = Kite::cliApp()->getInput()->getWorkDir();

        // add to log
        $record['extra']['workDir'] = $workDir;

        return $record;
    }
}
