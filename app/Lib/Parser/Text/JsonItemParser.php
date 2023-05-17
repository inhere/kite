<?php declare(strict_types=1);

namespace Inhere\Kite\Lib\Parser\Text;

use Toolkit\Stdlib\Str;
use function preg_match;
use function str_contains;

/**
 * class JsonItemParser
 *
 * @author inhere
 */
class JsonItemParser
{
    public string $keyField = 'field';

    public string $keyComment = 'comment';

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
    public function __invoke(string $line): array
    {
        if ($matches = self::matchField($line)) {
            return [];
        }

        return [
            $this->keyField => $matches[1],
            $this->keyComment => Str::toLowerWords($matches[1]),
        ];
    }

    /**
     * @param string $line
     *
     * @return array
     */
    public static function matchField(string $line): array
    {
        // is value of array.
        if (!str_contains($line, '":')) {
            return [];
        }

        $ok = preg_match('/^\s*[\'"]?([a-zA-Z][\w_]+)/', $line, $matches);

        return $ok ? $matches : [];
    }

    /**
     * @param string $keyField
     */
    public function setKeyField(string $keyField): void
    {
        $this->keyField = $keyField;
    }
}
