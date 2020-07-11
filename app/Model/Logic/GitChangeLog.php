<?php declare(strict_types=1);

namespace Inhere\Kite\Model\Logic;

/**
 * Class GitChangeLog
 *
 * @package Inhere\Kite\Model\Logic
 */
class GitChangeLog
{
    public static function new(): self
    {
        return new self();
    }

    public function parse(): self
    {
        return $this;
    }

    public function getChangeLog(): array
    {
        return [];
    }

    public function toArray(): array
    {
        return [];
    }

    public function export(): void
    {

    }
}
