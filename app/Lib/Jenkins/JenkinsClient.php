<?php declare(strict_types=1);

namespace Inhere\Kite\Lib\Jenkins;

use PhpPkg\Http\Client\AbstractClient;
use PhpPkg\Http\Client\Client;
use Toolkit\Stdlib\Obj\AbstractObj;
use function strtr;

/**
 * class JenkinsClient
 *
 * @author inhere
 */
class JenkinsClient extends AbstractObj
{
    /**
     * @var AbstractClient|null
     */
    private ?AbstractClient $httpClient;

    /**
     * @var string
     */
    public string $hostUrl = '';

    /**
     * Jenkins username
     *
     * @var string
     */
    public string $username = '';

    /**
     * @var string
     */
    public string $apiToken = '';

    /**
     * eg: /some
     *
     * @var string
     */
    public string $folderPath = '';

    /**
     * @var string
     */
    public string $jobName = '';

    /**
     * @param string $jobName
     *
     * @return $this
     */
    public function withJobName(string $jobName): self
    {
        $this->jobName = $jobName;
        return $this;
    }

    /**
     * @param array $params
     *
     * @return string
     */
    public function buildWithParams(array $params): string
    {
        $tpl = '{folderPath}/job/{name}/buildWithParameters';
        $url = $this->buildUrl($tpl);
        $cli = $this->getHttpClient()->post($url, $params);

        return $cli->getResponseBody();
    }

    public function buildNoParams(): self
    {
        $tpl = '{folderPath}/job/{name}/build';
        $url = $this->buildUrl($tpl);

        return $this;
    }

    /**
     * @param string $pathTpl
     *
     * @return string
     */
    public function buildUrl(string $pathTpl): string
    {
        return $this->hostUrl . strtr($pathTpl, [
            '{folderPath}' => $this->folderPath,
            '{name}'       => $this->jobName,
        ]);
    }

    /**
     * @param string $jobName
     *
     * @return string
     */
    public function jobPageUrl(string $jobName = ''): string
    {
        $jobName = $jobName ?: $this->jobName;

        return $this->hostUrl . '/job/' . $jobName;
    }

    /**
     * @return AbstractClient
     */
    public function getHttpClient(): AbstractClient
    {
        if (!$this->httpClient) {
            $this->httpClient = Client::factory([]);
        }

        return $this->httpClient;
    }

    /**
     * @param AbstractClient $httpClient
     */
    public function setHttpClient(AbstractClient $httpClient): void
    {
        $this->httpClient = $httpClient;
    }


}
