<?php declare(strict_types=1);

namespace Inhere\Kite\Console\Model;

use Toolkit\Stdlib\Obj\AbstractObj;

/**
 * class ToolCmdMeta
 *
 * @author inhere
 * @date 2022/5/16
 */
class ToolCmdMeta extends AbstractObj
{
    public string $name = '';

    protected array $run = [];
    protected array $deps = [];

    /**
     * @param array|string $deps
     */
    public function setDeps(array|string $deps): void
    {
        $this->deps = (array)$deps;
    }

    /**
     * @param array|string $run
     */
    public function setRun(array|string $run): void
    {
        $this->run = (array)$run;
    }
}
