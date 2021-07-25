<?php declare(strict_types=1);

namespace Inhere\Kite\Common\Template;

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
 * @package Inhere\Kite\Common\Template
 */
class SimpleTemplate extends AbstractTemplate
{
    /**
     * @var string
     */
    protected $varTpl = '{{%s}}';

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
     * @param string $tempFile
     * @param array  $vars
     *
     * @return string
     */
    public function renderFile(string $tempFile, array $vars): string
    {
        if (!file_exists($tempFile)) {
            throw new InvalidArgumentException('the template file is not exist. file:' . $tempFile);
        }

        $tplCode = file_get_contents($tempFile);

        return $this->renderString($tplCode, $vars);
    }

    /**
     * @param string $tplCode
     * @param array  $vars
     *
     * @return string
     */
    public function renderString(string $tplCode, array $vars): string
    {
        if ($this->globalVars) {
            $vars = array_merge($this->globalVars, $vars);
        }

        $fmtVars = [];
        foreach ($vars as $name => $var) {
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
