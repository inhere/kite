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
use Inhere\Console\Component\Interact\IShell;
use Inhere\Console\IO\Input;
use Inhere\Console\IO\Output;
use Inhere\Console\Util\Show;
use InvalidArgumentException;
use Symfony\Component\ExpressionLanguage\ExpressionFunction;
use Symfony\Component\ExpressionLanguage\ExpressionFunctionProviderInterface;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\VarExporter\Exception\ExceptionInterface;
use Symfony\Component\VarExporter\VarExporter;
use Throwable;
use Toolkit\Cli\Cli;
use Toolkit\Cli\Color;
use Toolkit\Stdlib\Str;
use Toolkit\Stdlib\Str\StrBuffer;
use function array_shift;
use function count;
use function is_numeric;
use function is_scalar;
use function strpos;
use function substr;
use function trim;

/**
 * Class DemoPlugin
 */
class ExprCommand extends Command
{
    protected static $name = 'expr';

    protected static $description = 'Use for expression calculation';

    /**
     * @var ExpressionLanguage
     */
    private $el;

    /**
     * @var mixed
     */
    // private $ret;

    /**
     * @var bool
     */
    private $debug = false;

    /**
     * @var array
     */
    private $vars = [];

    /**
     * @var array
     */
    private $histories = [];

    /**
     * @var int
     */
    private $historyNum = 100;

    public static function aliases(): array
    {
        return ['calc'];
    }

    protected function createELObject(): void
    {
        $provider = new class implements ExpressionFunctionProviderInterface {
            public function getFunctions(): array
            {
                return [
                    /** @see time() */
                    ExpressionFunction::fromPhp('time'),
                    /** @see date() */
                    ExpressionFunction::fromPhp('date'),
                    /** @see round() */
                    ExpressionFunction::fromPhp('round'),
                ];
            }
        };

        $this->el = new ExpressionLanguage(null, [
            $provider,
        ]);
    }

    /**
     * Do execute command
     *
     * @param Input  $input
     * @param Output $output
     *
     * @return int
     * @throws Throwable
     */
    protected function execute($input, $output): int
    {
        $this->createELObject();

        if ($input->getBoolOpt('i')) {
            $this->runByShell();
            return 0;
        }

        $args = $input->getArgs();
        $expr = implode(' ', $args);
        Color::println('Input expr: ' . $expr);

        $value = $this->el->evaluate($expr);
        // vdump($el->compile('1 + 2')); // displays (1 + 2)

        Color::println('Result:');
        echo VarExporter::export($value), PHP_EOL;

        return 0;
    }

    private function runByShell(): void
    {
        IShell::run(function ($expr) {
            if ($this->filterExpr($expr)) {
                return;
            }

            // evaluate
            $value = $this->el->evaluate($expr, $this->vars);

            // save last result.
            $this->vars['ret'] = $value;
            echo is_scalar($value) ? $value : VarExporter::export($value);
        }, [
            'prefix'      => 'EXPR',
            'validator'   => function (string $line) {
                if ($line === '') {
                    throw new InvalidArgumentException('input is empty!');
                }
                return $line;
            },
            'helpHandler' => $this->createHelpHandler(),
        ]);
    }

    /**
     * @return callable
     */
    protected function createHelpHandler(): callable
    {
        $buf = StrBuffer::new();
        $buf->writeln('<comment>Usage:</comment>');
        $buf->writeln('?, help  Display help message');
        $buf->writeln('@get     Get the setting of the shell program');
        $buf->writeln('          eg: `@get debug`   Get current debug mode');
        $buf->writeln('@set     Set for the shell program');
        $buf->writeln('          eg: `@set debug=true`   Switch debug mode');
        $buf->writeln('');
        $buf->writeln('<comment>Commands:</comment>');
        $buf->writeln('list         List all defined vars.');
        $buf->writeln('get          Get an define var value. eg: get name');
        $buf->writeln('set          Define an var. eg: set name=inhere');
        $buf->writeln('unset        Unset an defined var. eg: unset name');
        $buf->writeln('reset        Reset all defined vars. eg: reset');
        $buf->writeln('history      Display histories');

        return static function (array $keys) use ($buf) {
            Cli::write($buf->toString(), false);
        };
    }

    /**
     * @param string $expr
     *
     * @return bool
     * @throws ExceptionInterface
     */
    public function filterExpr(string $expr): bool
    {
        // comments line - ignore.
        if ($expr[0] === '#') {
            return true;
        }

        // history
        if ($expr === 'history' || strpos($expr, 'history ') === 0) {
            Cli::writeln('Total: ' . count($this->histories));
            Cli::writeln('------------------------------');
            Cli::write($this->histories);
            return true;
        }

        // record history
        $this->histories[] = $expr;
        if (count($this->histories) > $this->historyNum) {
            array_shift($this->histories);
        }

        // list var
        if ($expr === 'list') {
            Show::aList($this->vars, 'defined vars:', [
                'lastNewline' => false,
            ]);
            return true;
        }

        // set an var
        if (strpos($expr, 'set ') === 0) {
            $subExpr = trim(substr($expr, 4));
            if (!$this->setVarByExpr($subExpr)) {
                Show::liteError("Define an var like: set name = inhere");
            } else {
                echo 'OK';
            }
            return true;
        }

        // get var value
        if (strpos($expr, 'get ') === 0) {
            [, $var] = Str::toArray($expr, ' ');
            if (!$this->showVarValue($var)) {
                Show::liteError("Get an not defined var: $var");
            }
            return true;
        }

        // unset var
        if (strpos($expr, 'unset ') === 0) {
            [, $var] = Str::toArray($expr, ' ');

            if (!$this->unsetVar($var)) {
                Show::liteError("unset not exist var: $var");
            } else {
                Show::liteInfo("unset var: $var");
            }
            return true;
        }

        // reset vars
        if ($expr === 'reset ') {
            $this->vars = [];
            Show::liteInfo('reset all vars');
            return true;
        }

        // is an var name.
        if (strpos($expr, ' ') === false && $this->showVarValue($expr)) {
            return true;
        }

        // goon to eval parse.
        return false;
    }

    /**
     * @param string $var
     *
     * @return bool
     * @throws ExceptionInterface
     */
    protected function showVarValue(string $var): bool
    {
        if (isset($this->vars[$var])) {
            $val = $this->vars[$var];
            echo is_scalar($val) ? $val : VarExporter::export($val);
            return true;
        }

        return false;
    }

    /**
     * @param string $expr eg: `name=inhere`
     *
     * @return bool
     */
    protected function setVarByExpr(string $expr): bool
    {
        if (strpos($expr, '=') < 2) {
            return false;
        }

        [$var, $value] = Str::toArray($expr, '=');
        if (is_numeric($value)) {
            $value = (int)$value;
        }

        $this->vars[$var] = $value;
        return true;
    }

    /**
     * @param string $var
     *
     * @return bool
     */
    protected function unsetVar(string $var): bool
    {
        $var = trim($var);
        if (isset($this->vars[$var])) {
            unset($this->vars[$var]);
            return true;
        }

        return false;
    }
}
