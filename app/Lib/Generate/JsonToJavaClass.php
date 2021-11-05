<?php declare(strict_types=1);

namespace Inhere\Kite\Lib\Generate;

use function random_int;

/**
 * class JsonToJavaClass
 * - json(5) to java DTO class
 */
class JsonToJavaClass extends AbstractJsonToCode
{
    public const TYPE = 'java';

    /**
     * @return string
     */
    public function generate(): string
    {
        // todo
        $this->contexts['pkgName'] = '';

        return parent::generate();
    }

}
