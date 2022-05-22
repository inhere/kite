<?php declare(strict_types=1);

namespace Inhere\Kite\Lib\Parser\Text;

use Toolkit\Stdlib\Str;
use function in_array;
use function preg_replace;
use function strpos;
use function substr;

/**
 * class Json5LineParser - parse json5 line, get field and comments
 */
class Json5ItemParser extends JsonItemParser
{
    /**
     * @param string $line
     *
     * @return array
     */
    public static function parse(string $line): array
    {
        $fn = new self();
        return $fn($line);
    }

    /**
     * @param string $line
     *
     * @return array
     */
    public function __invoke(string $line): array
    {
        $pos = strpos($line, '//');
        if ($pos < 1) {
            // fallback parse json line
            if ($matches = self::matchField($line)) {
                return [
                    $this->keyField   => $matches[1],
                    $this->keyComment => Str::toLowerWords($matches[1]),
                ];
            }
            return [];
        }

        // url
        if (str_contains($line, '://')) {
            $line = preg_replace('/https?:\/\//', 'XX', $line);
            $pos  = strpos($line, '//');
            if ($pos < 1) {
                return [];
            }
        }

        // match field
        if (!$matches = self::matchField($line)) {
            return [];
        }

        $field = $matches[1];
        if ($this->exclude && in_array($field, $this->exclude, true)) {
            return [];
        }

        $item = [
            $this->keyField   => $field,
            $this->keyComment => Str::toLowerWords($field),
        ];

        // get comments
        if ($comment = trim(substr($line, $pos + 2))) {
            $item[$this->keyComment] = $comment;
        }

        return $item;
    }
}
