<?php declare(strict_types=1);

namespace Inhere\Kite\Lib\Convert;

/**
 * class AbstractConverter
 */
abstract class AbstractConverter
{
    abstract public function convert(): string;

}
