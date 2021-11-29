<?php declare(strict_types=1);

namespace Inhere\Kite\Lib\Parser\Text;

use Toolkit\Stdlib\Obj\Traits\QuickInitTrait;
use Toolkit\Stdlib\Str;
use Toolkit\Stdlib\Type;
use function is_numeric;
use function ltrim;
use function substr;
use function trim;

/**
 * class PhpConstInlineParser
 *
 * @author inhere
 */
class PhpConstInlineParser
{
    use QuickInitTrait;

    /**
     * @var bool
     */
    public bool $withKey = true;

    /**
     * @var string
     */
    public string $valueType = '';

    /**
     * @param string $str
     *
     * @return array
     */
    public function __invoke(string $str): array
    {
        // example line:
        // 'public const STATUS           = 1;        // 正常'

        // invalid
        if (!str_starts_with($str, 'public const ')) {
            return [];
        }

        [$name, $other] = Str::explode(trim(substr($str, 13)), '=', 2);
        [$value, $comment] = Str::explode($other, ';', 2);

        $value = ltrim($value, '= ');
        if (!$this->valueType) {
            $this->valueType = is_numeric($value) ? Type::INTEGER : Type::STRING;
        }

        $comment = ltrim($comment, '// ');
        if (!$this->withKey) {
            return [$name, $value, $comment];
        }

        return [
            'name'    => $name,
            'value'   => $value,
            'comment' => $comment
        ];
    }
}
