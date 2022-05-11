<?php declare(strict_types=1);

namespace Inhere\Kite\Common\Log;

use Inhere\Kite\Kite;
use Monolog\LogRecord;
use Monolog\Processor\ProcessorInterface;
use Toolkit\Stdlib\OS;

/**
 * Class CliLogProcessor
 *
 * @package Inhere\Kite\Common\Log
 * @psalm-template Record
 */
class CliLogProcessor implements ProcessorInterface
{
    /**
     * @param LogRecord $record
     *
     * @return LogRecord The processed record
     */
    public function __invoke(LogRecord $record): LogRecord
    {
        $workDir = Kite::cliApp()->getInput()->getWorkDir();

        // add to log
        $record->extra['OSName'] = OS::name();
        $record->extra['workDir'] = $workDir;

        return $record;
    }
}
