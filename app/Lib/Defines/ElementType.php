<?php declare(strict_types=1);

namespace Inhere\Kite\Lib\Defines;

/**
 * program element type
 *
 * @author inhere
 */
class ElementType
{
    /**
     * Class, interface (including annotation type), or enum declaration
     */
    public const TYPE = 'type';

    /**
     * Field declaration (includes enum constants)
     */
    public const FIELD = 'field';

    public const METHOD = 'method';

    /** Formal parameter declaration */
    public const PARAMETER = 'parameter';

    public const ANNOTATION = 'annotation';

    // -------  sub for type -------

    public const TYPE_CLASS = 'class';

    public const TYPE_ENUM = 'enum';

    public const TYPE_INTERFACE = 'interface';

}