<?php declare(strict_types=1);

namespace Inhere\Kite\Common;

use Toolkit\Cli\Color;
use Toolkit\Sys\Cmd\CmdBuilder;

/**
 * class Cmd builder
 */
class Cmd extends CmdBuilder
{
    /**
     * @param string $msg
     * @param string $scene
     */
    protected function printMessage(string $msg, string $scene): void
    {
        self::printByScene($msg, $scene);
    }

    /**
     * @param string $msg
     * @param string $scene
     */
    public static function printByScene(string $msg, string $scene): void
    {
        $color = 'info';
        if ($scene === self::PRINT_CMD) {
            $color = 'yellow';
        } elseif ($scene === self::PRINT_DRY_RUN) {
            $color = 'cyan';
        } elseif ($scene === self::PRINT_ERROR) {
            $color = 'red';
        }

        Color::println($msg, $color);
    }
}
