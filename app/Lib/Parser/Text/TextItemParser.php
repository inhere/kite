<?php declare(strict_types=1);

namespace Inhere\Kite\Lib\Parser\Text;

use Toolkit\Stdlib\Str;
use function array_combine;
use function count;
use function in_array;

/**
 * class TextItemParser
 *
 * @author inhere
 */
class TextItemParser
{
    /**
     * @var string
     */
    public string $valSep = ' ';

    /**
     * Only collect given indexes cols., start is 0.
     *
     * @var list<int>
     */
    public array $indexes = [];

    /**
     * Field names.
     *
     * @var array
     */
    public array $fields = [];

    /**
     * @param string $valSep
     * @param array $indexes
     * @param array $fields
     *
     * @return static
     */
    public static function new(string $valSep = ' ', array $indexes = [], array $fields = []): self
    {
        $self = new self();

        $self->valSep  = TextParser::resolveSep($valSep);
        $self->indexes = $indexes;
        $self->fields  = $fields;
        return $self;
    }

    /**
     * @param string $str
     * @param int $fieldNum
     *
     * @return array
     */
    public function __invoke(string $str, int $fieldNum): array
    {
        $wants = $values = Str::toNoEmptyArray($str, $this->valSep, $fieldNum);

        // only collect given indexes cols.
        if ($this->indexes) {
            $wants = [];
            foreach ($values as $i => $line) {
                if (in_array($i, $this->indexes, true)) {
                    $wants[] = $line;
                }
            }
        }

        $nameNum = count($this->fields);
        if ($nameNum > 0) {
            // vdump($this->fields, $wants);
            $wants = array_combine($this->fields, $wants);
        }

        return $wants;
    }
}
