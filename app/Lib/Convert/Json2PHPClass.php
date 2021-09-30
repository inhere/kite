<?php declare(strict_types=1);

namespace Inhere\Kite\Lib\Convert;

use function random_int;

/**
 * class Json2PHPClass
 */
class Json2PHPClass extends AbstractConverter
{
    /**
     * @var string
     */
    protected $source = '';

    /**
     * @var string
     */
    protected $className = '';

    /**
     * @return string
     */
    public function convert(): string
    {
        $data = json_decode($json, true);

        return $this->dataToClass($data);
    }

    protected function dataToClass(array $data): string
    {
        $props = [];
        foreach ($data as $field => $value) {
            // var_dump(gettype($value));
            switch (gettype($value)) {
                case 'integer':
                    $props[] = <<<PROP
    /**
     * @var int
     */
    public \$$field = 0;
PROP;
                    break;

                case 'string':
                    $props[] = <<<PROP
    /**
     * @var string
     */
    public \$$field = '';
PROP;
                    break;

                case 'array':
                    $props[] = <<<PROP
    /**
     * @var array
     */
    public \$$field = [];
PROP;
                    break;

                default:
                    $props[] = <<<PROP
    /**
     * @var mixed
     */
    public \$$field;
PROP;
                    break;
            }
        }

        // var_dump($props);
        $propsString = implode("\n\n", $props);

        $className = $this->className;
        if (!$className) {
            $className = 'AutoGen_Class' . random_int(100, 999);
        }

        $content = <<<CODE
/**
 * Class $className
 */
class $className
{
$propsString
}

CODE;

        return $content;
    }
}
