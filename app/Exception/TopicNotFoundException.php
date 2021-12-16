<?php declare(strict_types=1);

namespace Inhere\Kite\Exception;

use RuntimeException;
use Throwable;

/**
 * Class TopicNotFoundException
 *
 * @package Inhere\Kite\Exception
 */
class TopicNotFoundException extends RuntimeException
{
    /**
     * @var string
     */
    private string $name;

    /**
     * @var string
     */
    private string $parentName;

    /**
     * Class constructor.
     *
     * @param string $name
     * @param string $parentName
     * @param int    $code
     */
    public function __construct(string $name, string $parentName = '', int $code = 404)
    {
        if ($parentName) {
            $message = "the topic name '{$name}'(parent:$parentName) is not found";
        } else {
            $message = "the topic name '{$name}' is not found";
        }

        parent::__construct($message, $code);

        $this->name = $name;
        $this->parentName = $parentName;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getParentName(): string
    {
        return $this->parentName;
    }
}
