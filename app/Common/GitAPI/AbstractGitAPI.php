<?php declare(strict_types=1);

namespace Inhere\Kite\Common\GitAPI;

use PhpPkg\Http\Client\AbstractClient;
use PhpPkg\Http\Client\Client;
use Toolkit\Stdlib\Obj\AbstractObj;
use function explode;
use function implode;
use function sprintf;

/**
 * class AbstractGitAPI
 */
abstract class AbstractGitAPI extends AbstractObj
{
    public const DEFAULT_UA = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/64.0.3282.119 Safari/537.36';

    /**
     * base API url
     *
     * @var string
     */
    protected string $baseApi = 'https://gitlab.example.com/api/v4';

    /**
     * The repository owner user/group
     *
     * @var string
     */
    protected string $group = '';

    /**
     * The repository name
     *
     * @var string
     */
    protected string $repo = '';

    /**
     * Gitlab/Github person access token
     *
     * @see https://github.com/settings/tokens on Github
     * @see https://HOST/profile/personal_access_tokens on Gitlab
     * @var string
     */
    protected string $token = '';

    /**
     * @param string $group "gookit"
     *
     * @return $this
     */
    public function withGroup(string $group): self
    {
        $self = clone $this;

        $self->group = $group;

        return $self;
    }

    /**
     * @param string $owner
     *
     * @return $this
     */
    public function withOwner(string $owner): self
    {
        return $this->withGroup($owner);
    }

    /**
     * @param string $repo "color"
     *
     * @return $this
     */
    public function withRepo(string $repo): self
    {
        $self = clone $this;

        $self->repo = $repo;

        return $self;
    }

    /**
     * @param string $owner "gookit"
     * @param string $repo  "color"
     *
     * @return $this
     */
    public function withOwnerRepo(string $owner, string $repo): self
    {
        return $this->withGroupRepo($owner, $repo);
    }

    /**
     * @param string $group "gookit"
     * @param string $repo  "color"
     *
     * @return $this
     */
    public function withGroupRepo(string $group, string $repo): self
    {
        $self = clone $this;

        $self->group = $group;
        $self->repo  = $repo;

        return $self;
    }

    /**
     * @param string $repoPath eg: "gookit/color"
     *
     * @return $this
     */
    public function withPathRepo(string $repoPath): self
    {
        [$owner, $repo] = explode('/', $repoPath, 2);

        return $this->withOwnerRepo($owner, $repo);
    }

    /**
     * @param string|int ...$nodes
     *
     * @return string
     */
    public function buildPath(...$nodes): string
    {
        return implode('/', $nodes);
    }

    /**
     * @param string     $format
     * @param string|int ...$args
     *
     * @return string
     */
    public function buildPathf(string $format, ...$args): string
    {
        return sprintf($format, ...$args);
    }

    /**
     * @return AbstractClient
     */
    public function newClient(): AbstractClient
    {
        // $cli = new HttpClient();
        $cli = Client::factory([]);
        $cli->setOptions([
            'headers' => [
                // github
                // 'Authorization' => 'Basic ' . $this->token,
                // 'Authorization' => 'Token ' . $this->token,
                // gitlab
                'Private-Token' => $this->token,
                'User-Agent'    => self::DEFAULT_UA,
                'Content-Type'  => 'application/json',
            ],
        ]);

        // $cli->setDebug(true);

        return $cli;
    }

    /**
     * @param string $method
     * @param string $uri
     * @param array $data
     *
     * @return AbstractClient
     */
    public function sendThen(string $method, string $uri, array $data = []): AbstractClient
    {
        return $this->newClient()->request($this->baseApi . $uri, $data, $method);
    }

    /**
     * @param string $method
     * @param string $uri
     * @param array $data
     *
     * @return array
     */
    public function sendRequest(string $method, string $uri, array $data = []): array
    {
        return $this->sendThen($method, $uri, $data)->getJsonArray();
    }

    /**
     * @param string $uri
     * @param array $query
     *
     * @return array
     */
    public function sendGET(string $uri, array $query = []): array
    {
        return $this->sendRequest('GET', $uri, $query);
    }

    /**
     * @param string $uri
     * @param array  $data
     *
     * @return array
     */
    public function sendPOST(string $uri, array $data): array
    {
        // curl -u username:token https://api.github.com/user
        // curl -H "Authorization: token OAUTH-TOKEN" https://api.github.com
        return $this->sendRequest('POST', $uri, $data);
    }

    /**
     * @return string
     */
    public function getToken(): string
    {
        return $this->token;
    }

    /**
     * @param string $token
     */
    public function setToken(string $token): void
    {
        $this->token = $token;
    }

    /**
     * @return string
     */
    public function getRepo(): string
    {
        return $this->repo;
    }

    /**
     * @param string $repo
     */
    public function setRepo(string $repo): void
    {
        $this->repo = $repo;
    }

    /**
     * @return string
     */
    public function getGroup(): string
    {
        return $this->group;
    }

    /**
     * @return string
     */
    public function getOwner(): string
    {
        return $this->group;
    }

    /**
     * @param string $group
     */
    public function setGroup(string $group): void
    {
        $this->group = $group;
    }

    /**
     * @return string
     */
    public function getBaseApi(): string
    {
        return $this->baseApi;
    }

    /**
     * @param string $baseApi
     */
    public function setBaseApi(string $baseApi): void
    {
        $this->baseApi = $baseApi;
    }
}
