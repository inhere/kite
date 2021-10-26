<?php declare(strict_types=1);

namespace Toolkit\PFlag\Helper;

use Toolkit\PFlag\FlagsParser;

/**
 * class FlagValCollector
 * - collect value by start i-shell env.
 */
class FlagValCollector
{
    /**
     * @return $this
     */
    public function new(): self
    {
        return new self();
    }

    /**
     * @param FlagsParser $fs
     */
    public function collect(FlagsParser $fs): void
    {
        // for ($fs->getOptDefine($name))
    }
}
