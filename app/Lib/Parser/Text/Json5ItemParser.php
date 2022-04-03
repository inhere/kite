<?php declare(strict_types=1);

namespace Inhere\Kite\Lib\Parser\Text;

use function in_array;
use function preg_match;
use function preg_replace;
use function strpos;
use function substr;

/**
 * class Json5LineParser - parse json5 line, get field and comments
 */
class Json5ItemParser
{
    public const KEY_FIELD  = 'field';
    public const KEY_COMMENT = 'comment';

    /**
     * exclude fields
     *
     * @var array<string>
     */
    public array $exclude = [];

    /**
     * @param array $exclude
     *
     * @return static
     */
    public static function new(array $exclude = []): self
    {
        $self = new self();

        $self->exclude = $exclude;
        return $self;
    }

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
        if (!preg_match('/^\s*[\'"]?([a-zA-Z][\w_]+)/', $line, $matches)) {
            return [];
        }

        $field = $matches[0];
        $item  = [
            'field' => $field,
        ];
        if ($this->exclude && in_array($field, $this->exclude, true)) {
            return [];
        }

        // get comments
        if (!$comment = trim(substr($line, $pos + 2))) {
            return [];
        }

        $item['comment'] = $comment;
        return $item;
    }
}
