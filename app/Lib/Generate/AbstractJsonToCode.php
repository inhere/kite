<?php declare(strict_types=1);

namespace Inhere\Kite\Lib\Generate;

use Inhere\Kite\Lib\Defines\DataField\JsonField;
use InvalidArgumentException;
use function trim;

/**
 * class AbstractJsonToCode
 */
abstract class AbstractJsonToCode extends AbstractGenCode
{
    /**
     * Source json(5) codes
     *
     * @var string
     */
    protected string $source = '';

    /**
     * @var array
     */
    // private array $jsonData = [];

    /**
     * @var array<string, JsonField>
     */
    // protected array $fields = [];

    /**
     * @return AbstractJsonToCode
     */
    public function prepare(): self
    {
        if ($this->isPrepared()) {
            return $this;
        }

        parent::prepare();

        $json = $this->source;
        if (!$json = trim($json)) {
            throw new InvalidArgumentException('empty source json(5) data for generate');
        }

        $jd = Json5Data::new()->loadFrom($json);

        $this->fields = $jd->getFields();
        $this->setContexts($jd->getSettings());

        return $this;
    }

    /**
     * @param string $source
     *
     * @return self
     */
    public function setSource(string $source): self
    {
        $this->source = $source;
        return $this;
    }

}
