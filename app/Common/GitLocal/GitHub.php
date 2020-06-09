<?php declare(strict_types=1);

namespace Inhere\Kite\Common\GitLocal;

use Inhere\Kite\Helper\GitUtil;
use RuntimeException;

/**
 * Class GitHub
 *
 * @package Inhere\Kite\Common\Git
 */
class GitHub extends AbstractGitLocal
{
    /**
     * Class constructor.
     *
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        parent::__construct($config);

        $this->host = self::GITHUB_HOST;
    }

    /**
     * @param string $host
     */
    public function setHost(string $host): void
    {
        throw new RuntimeException('The host is fixed for github, cannot change it.');
    }
}
