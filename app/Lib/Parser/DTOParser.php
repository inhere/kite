<?php declare(strict_types=1);

namespace Inhere\Kite\Lib\Parser;

use Inhere\Kite\Lib\Defines\ClassMeta;
use Inhere\Kite\Lib\Defines\ProgramLang;
use Inhere\Kite\Lib\Parser\Java\JavaDTOParser;
use InvalidArgumentException;
use function preg_match;

/**
 * @author inhere
 */
final class DTOParser
{
    /**
     * key: language name
     * value: parser class
     *
     * @var array<string, AbstractDTOParser>
     */
    private static array $parsers = [
        ProgramLang::JAVA => JavaDTOParser::class,
    ];

    /**
     * @param string $lang
     *
     * @return AbstractDTOParser
     */
    public static function create(string $lang): AbstractDTOParser
    {
        $class = self::$parsers[$lang] ?? null;
        if ($class) {
            return new $class();
        }

        throw new InvalidArgumentException("unsupported parser for language: $lang");
    }

    /**
     * @param string $lang
     * @param string $content
     *
     * @return ClassMeta or subclass
     */
    public static function parse(string $lang, string $content): ClassMeta
    {
        $p = self::create($lang);
        $p->setContent($content);
        return $p->doParse();
    }

    /**
     * @param string $lang
     * @param string $filePath
     *
     * @return ClassMeta or subclass
     */
    public static function parseFile(string $lang, string $filePath): ClassMeta
    {
        $content = file_get_contents($filePath);
        return self::parse($lang, $content);
    }

    /**
     * @param string $s
     *
     * @return bool
     */
    public static function isClassType(string $s): bool
    {
        return preg_match('#^(class|interface|enum)$#', $s) === 1;
    }

    /**
     * @param string $s
     *
     * @return bool
     */
    public static function isAccessModifier(string $s): bool
    {
        return preg_match('#^(public|private|protected)$#i', $s) === 1;
    }

    public static function isOtherModifier(string $s): bool
    {
        return preg_match('#^(final|static|abstract)$#i', $s) === 1;
    }

}