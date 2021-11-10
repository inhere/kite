<?php declare(strict_types=1);

namespace Inhere\Kite\Lib\Generate;

use ColinODell\Json5\Json5Decoder;
use Inhere\Kite\Lib\Generate\Json\JsonField;
use Inhere\Kite\Lib\Parser\Text\Json5ItemParser;
use Inhere\Kite\Lib\Parser\Text\TextParser;
use Toolkit\Stdlib\Obj\AbstractObj;
use function gettype;
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
    private array $settings = [];

    /**
     * @var array<string, JsonField>
     */
    private array $fields = [];

    /**
     * @param string $json
     *
     * @return $this
     */
    public function loadFrom(string $json): self
    {
        // auto add quote char
        if ($json[0] !== '{') {
            $json = '{' . $json . "\n}";
        }

        $comments = [];
        $jsonData = Json5Decoder::decode($json, true);

        // has comments chars
        if (str_contains($json, '//')) {
            $p = TextParser::newWithParser($json, new Json5ItemParser())
                ->withConfig(function (TextParser $p) {
                    $p->headerSep = "\n//###\n";
                })
                ->setBeforeParseHeader(function (string $header) {
                    if ($pos = strpos($header, "//##\n")) {
                        $header = substr($header, $pos + 4);
                        $header = str_replace("\n//", '', $header);
                    }
                    return $header;
                })
                ->parse();

            $comments = $p->getStringMap('field', 'comment');
            $this->setSettings($p->getSettings());
        }

        foreach ($jsonData as $key => $value) {
            $this->fields[$key] = JsonField::new([
                'name' => $key,
                'type' => gettype($value),
                'desc' => $comments[$key] ?? $key,
            ]);
        }

        return  $this;
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
     * @return JsonField[]
     */
    public function getFields(): array
    {
        return $this->fields;
    }
}
