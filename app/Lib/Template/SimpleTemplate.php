<?php declare(strict_types=1);

namespace Inhere\Kite\Lib\Template;

use InvalidArgumentException;
use Toolkit\Stdlib\Obj;
use function array_merge;
use function file_exists;
use function file_get_contents;
use function sprintf;
use function strtr;

/**
 * Class SimpleTemplate
 *
 * @author inhere
 * @package Inhere\Kite\Lib\Template
 */
class SimpleTemplate extends AbstractTemplate
{
    /**
     * @var string
     */
    protected string $varTpl = '{{%s}}';

    /**
     * Class constructor.
     *
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        Obj::init($this, $config);
    }

    /**
     * @param string $tplFile
     * @param array  $tplVars
     *
     * @return string
     */
    public function renderFile(string $tplFile, array $tplVars): string
    {
        if (!file_exists($tplFile)) {
            throw new InvalidArgumentException('the template file is not exist. file:' . $tplFile);
        }

        $tplCode = file_get_contents($tplFile);

        return $this->renderString($tplCode, $tplVars);
    }

    /**
     * @param string $tplCode
     * @param array  $tplVars
     *
     * @return string
     */
    public function renderString(string $tplCode, array $tplVars): string
    {
        if ($this->globalVars) {
            $tplVars = array_merge($this->globalVars, $tplVars);
        }

        $fmtVars = [];
        foreach ($tplVars as $name => $var) {
            $name = sprintf($this->varTpl, (string)$name);
            // add
            $fmtVars[$name] = $var;
        }

        return strtr($tplCode, $fmtVars);
    }

    /**
     * @return string
     */
    public function getVarTpl(): string
    {
        return $this->varTpl;
    }

    /**
     * @param string $varTpl
     */
    public function setVarTpl(string $varTpl): void
    {
        $this->varTpl = $varTpl;
    }
}
