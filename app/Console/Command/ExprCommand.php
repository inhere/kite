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
use Inhere\Kite\Kite;
use InvalidArgumentException;
use Symfony\Component\ExpressionLanguage\ExpressionFunction;
use Symfony\Component\ExpressionLanguage\ExpressionFunctionProviderInterface;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\VarExporter\VarExporter;
use Throwable;
use Toolkit\Cli\Cli;
use Toolkit\Cli\Color;
use Toolkit\Cli\Util\Readline;
use Toolkit\Stdlib\Helper\DataHelper;
use Toolkit\Stdlib\Str;
use Toolkit\Stdlib\Str\StrBuffer;
use function array_shift;
use function count;
use function in_array;
use function is_numeric;
use function str_replace;
use function stripos;
use function strpos;
use function substr;
use function trim;
use const BASE_PATH;

/**
 * Class ExprCommand
 */
class ExprCommand extends Command
{
    protected static $name = 'expr';

    protected static $desc = 'Use for expression calculation';

    public const RESULT_VAR = 'ret';

    /**
     * @var ExpressionLanguage
     */
    private ExpressionLanguage $el;

    /**
     * @var bool
     */
    private $debug = false;

    /**
     * @var array
     */
    private array $vars = [];

    /**
     * @var array
     */
    private array $histories = [];

    /**
     * @var int
     */
    private int $historyNum = 100;

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
                    ExpressionFunction::fromPhp('time'), /** @see time() */
                    ExpressionFunction::fromPhp('date'), /** @see date() */
                    ExpressionFunction::fromPhp('round'), /** @see round() */
                    ExpressionFunction::fromPhp('ceil'), /** @see ceil() */
                    ExpressionFunction::fromPhp('floor'), /** @see floor() */
                ];
            }
        };

        $this->el = new ExpressionLanguage(null, [
            $provider,
        ]);
    }

    /**
     * @options
     *  -i, --interactive     bool;Start an interactive shell environment
     *
     * @param Input  $input
     * @param Output $output
     *
     * @return int
     * @throws Throwable
     * @example
     *  <code>$ {binWithCmd} -i</code>
     *  Expr > 45 + 45
     *  90
     *  Expr > ret * 2
     *  180
     *
     */
    protected function execute(Input $input, Output $output): int
    {
        $this->createELObject();

        if ($this->flags->getOpt('interactive')) {
            $this->runByShell();
            return 0;
        }

        $args = $this->flags->getRawArgs();
        $expr = implode(' ', $args);
        Color::println('Input expr: ' . $expr);

        $value = $this->el->evaluate($expr);
        Color::println('Result:');
        echo VarExporter::export($value), PHP_EOL;
        return 0;
    }

    /**
     * @throws Throwable
     */
    private function runByShell(): void
    {
        $sh = IShell::new([
            'prefix'      => 'Expr',
            'validator'   => function (string $line) {
                if ($line === '') {
                    throw new InvalidArgumentException('input is empty!');
                }
                return $line;
            },
            'helpHandler' => $this->createHelpHandler(),
            'historyFile' => BASE_PATH . '/tmp/expr-history.txt',
        ]);

        $sh->setHandler(function (string $expr) {
            if ($expr) {
                $firstChar = $expr[0];
                // starts with calc chars
                if (in_array($firstChar, ['+', '-', '*', '/'], true)) {
                    $expr = self::RESULT_VAR . ' ' . $expr;
                }
            }

            if ($this->filterExpr($expr)) {
                return;
            }

            // evaluate
            $value = $this->el->evaluate($expr, $this->vars);

            // save last result.
            $this->vars[self::RESULT_VAR] = $value;
            echo DataHelper::toString($value);
        });

        $sh->setAutoCompleter(function (string $input, int $index) {
            $commands = [
                '?',
                'help',
                'quit',
                'list',
                'get',
                'set',
                'unset',
                'history',
                self::RESULT_VAR,
            ];

            $info = Readline::getInfo();
            // $line contains $input
            $line = trim(substr($info['line_buffer'], 0, $info['end']));
            if ($info['point'] !== $info['end']) {
                return true;
            }

            if (!$line) {
                return $commands;
            }

            $founded = [];

            // $line=$input completion for top command name prefix.
            if (Str::contains($line, ' ') === false) {
                foreach ($commands as $name) {
                    if (stripos($name, $input) !== false) {
                        $founded[] = $name;
                    }
                }

                Kite::logger()->info("expr - input keywords '$input' for complete", [
                    'index'   => $index,
                    'founded' => $founded,
                    'rlInfo'  => $info,
                ]);
            } else { // completion for subcommand
                // todo ...
            }

            return $founded ?: $commands;
        });

        $sh->start();
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
        $buf->writeln('q,quit   Quit shell');
        $buf->writeln('');
        $buf->writeln('<comment>Commands:</comment>');
        $buf->writeln('list         List all defined vars.');
        $buf->writeln('get          Get an define var value. eg: get name');
        $buf->writeln('set          Define an var. eg: set name=inhere');
        $buf->writeln('unset        Unset an defined var. eg: unset name');
        $buf->writeln('reset        Reset all defined vars. eg: reset');
        $buf->writeln('history      Display histories');

        return static function () use ($buf) {
            Cli::write($buf->toString(), false);
        };
    }

    /**
     * @param string $expr
     *
     * @return bool
     */
    public function filterExpr(string $expr): bool
    {
        // comments line - ignore.
        if ($expr[0] === '#') {
            return true;
        }

        // history
        if ($expr === 'history' || Str::hasPrefix($expr, 'history ')) {
            Cli::writeln('Total: ' . count($this->histories));
            Cli::writeln('------------------------------');
            Cli::write($this->histories);
            return true;
        }

        // record history
        $var = self::RESULT_VAR;
        if (isset($this->vars[$var]) && Str::contains($expr, $var)) {
            $this->histories[] = str_replace($var, (string)$this->vars[$var], $expr);
        } else {
            $this->histories[] = $expr;
        }

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
        if (Str::hasPrefix($expr, 'set ')) {
            $subExpr = trim(substr($expr, 4));
            if (!$this->setVarByExpr($subExpr)) {
                Show::liteError("Define an var like: set name = inhere");
            } else {
                echo 'OK';
            }
            return true;
        }

        // get var value
        if (Str::hasPrefix($expr, 'get ')) {
            [, $var] = Str::toArray($expr, ' ');
            if (!$this->showVarValue($var)) {
                Show::liteError("Get an not defined var: $var");
            }
            return true;
        }

        // unset var
        if (Str::hasPrefix($expr, 'unset ')) {
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
        if (Str::contains($expr, ' ') === false && $this->showVarValue($expr)) {
            return true;
        }

        // goon to eval parse.
        return false;
    }

    /**
     * @param string $var
     *
     * @return bool
     */
    protected function showVarValue(string $var): bool
    {
        if (isset($this->vars[$var])) {
            echo DataHelper::toString($this->vars[$var]);
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
