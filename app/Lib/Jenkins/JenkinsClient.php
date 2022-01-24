<?php declare(strict_types=1);

namespace Inhere\Kite\Lib\Jenkins;

use Psr\Http\Client\ClientInterface;
use Toolkit\Stdlib\Obj\Traits\QuickInitTrait;

/**
 * class JenkinsClient
 *
 * @author inhere
 */
class JenkinsClient
{
    use QuickInitTrait;

    /**
     * @var ClientInterface
     */
    private ClientInterface $httpClient;

    /**
     * @var string
     */
    public string $baseUrl = '';

    /**
     * @return ClientInterface
     */
    public function getHttpClient(): ClientInterface
    {
        return $this->httpClient;
    }

    /**
     * @param ClientInterface $httpClient
     */
    public function setHttpClient(ClientInterface $httpClient): void
    {
        $this->httpClient = $httpClient;
    }


}
