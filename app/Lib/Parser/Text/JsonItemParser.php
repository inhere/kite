<?php declare(strict_types=1);

namespace Inhere\Kite\Lib\Parser\Text;

use function preg_match;

/**
 * class JsonItemParser
 *
 * @author inhere
 */
class JsonItemParser
{
    public const KEY_FIELD  = 'field';

    /**
     * @param string $str
     *
     * @return array
     */
    public function __invoke(string $str): array
    {
        if (!preg_match('/[a-zA-Z][\w_]+/', $str, $matches)) {
            return [];
        }

        return [self::KEY_FIELD => $matches[1]];
    }
}
