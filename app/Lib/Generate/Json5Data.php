<?php declare(strict_types=1);

namespace Inhere\Kite\Lib\Generate;

use ColinODell\Json5\Json5Decoder;
use ColinODell\Json5\SyntaxError;
use Inhere\Kite\Lib\Defines\DataField\JsonField;
use Inhere\Kite\Lib\Parser\Text\Json5ItemParser;
use Inhere\Kite\Lib\Parser\TextParser;
use Toolkit\Stdlib\Obj\AbstractObj;
use Toolkit\Stdlib\Type;
use function count;
use function gettype;
use function is_array;
use function str_replace;
use function strpos;
use function substr;

/**
 * class Json5Data
 *
 * @author inhere
 */
class Json5Data extends AbstractObj
{
    /**
     * @var array<string, mixed>
     */
    private array $settings = [];

    /**
     * @var array<string, string>
     */
    private array $comments = [];

    /**
     * @var array<string, JsonField>
     */
    private array $fields = [];

    /**
     * Sub-objects in the data
     *
     * ```json
     * {
     *  ObjectName: {
     *      FieldName: JsonField,
     *  },
     * }
     * ```
     *
     * @var array<string, array<string, JsonField>>
     */
    private array $subObjects = [];

    /**
     * @param string $json
     *
     * @return $this
     * @throws SyntaxError
     */
    public function loadFrom(string $json): self
    {
        // has comments chars
        if (str_contains($json, '//')) {
            $p = TextParser::newWithParser($json, new Json5ItemParser())
                ->withConfig(function (TextParser $p) {
                    $p->headerSep = "\n//###\n";
                })
                ->setBeforeParseHeader(function (string $header) {
                    if ($pos = strpos($header, "//##\n")) {
                        $header = substr($header, $pos + 4);
                        $header = str_replace("\n//", "\n", $header);
                    }

                    return $header;
                })
                ->parse();

            $this->comments = $p->getStringMap('field', 'comment');
            $this->setSettings($p->getSettings());
            // get no header json
            $json = $p->getTextBody();
        }

        // auto add quote char
        if ($json[0] !== '{') {
            $json = '{' . $json . "\n}";
        }

        $jsonData = Json5Decoder::decode($json, true);
        $this->collectObjectFields('', $jsonData);
        $this->comments = [];

        return $this;
    }

    /**
     * @param string $name
     * @param array $map
     */
    protected function collectObjectFields(string $name, array $map): void
    {
        $fields = [];
        foreach ($map as $key => $value) {
            $type = gettype($value);

            $elemType = $elemSfx = '';
            if ($type === 'array' && !empty($value)) {
                $elemType = $key;
                if (isset($this->subObjects[$key])) {
                    $elemSfx = '_' . count($this->subObjects);
                }

                // is object
                if (!isset($value[0])) {
                    $type = Type::OBJECT;
                    $this->collectObjectFields($key . $elemSfx, $value);
                } elseif (!empty($value[0])) { // is array
                    // collect first item on it's object
                    if (is_array($value[0])) {
                        $elemSfx = 'Item' . $elemSfx;
                        $this->collectObjectFields($key . $elemSfx, $value[0]);
                    } else {
                        $elemType = gettype($value[0]);
                    }
                }
            }

            $fields[$key] = JsonField::new([
                'name'    => $key,
                'type'    => $type,
                'subType' => $elemType . $elemSfx,
                'comment' => $this->comments[$key] ?? $key,
            ]);
        }

        if ($name) {
            $this->subObjects[$name] = $fields;
        } else {
            $this->fields = $fields;
        }
    }

    /**
     * @return array
     */
    public function getSettings(): array
    {
        return $this->settings;
    }

    /**
     * @param array $settings
     */
    public function setSettings(array $settings): void
    {
        $this->settings = $settings;
    }

    /**
     * @return array<string, JsonField>
     */
    public function getFields(): array
    {
        return $this->fields;
    }

    /**
     * @return array<string, array<string, JsonField>>
     */
    public function getSubObjects(): array
    {
        return $this->subObjects;
    }
}
