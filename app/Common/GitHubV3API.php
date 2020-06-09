<?php declare(strict_types=1);

namespace Inhere\Kite\Common;

use Swoft\Swlib\HttpClient;
use Toolkit\Stdlib\Str\JsonHelper;
use function explode;
use function sprintf;

/**
 * Class GitHubV3API
 */
class GitHubV3API
{
    public const BASE_API_URL = 'https://api.github.com';

    public const BASE_REPOS_URL = 'https://api.github.com/repos';

    // POST /repos/:owner/:repo/issues/:issue_number/comments
    public const ADD_ISSUE_COMMENT = '/repos/%s/%s/issues/%d/comments';

    public const DEF_USER_AGENT = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/64.0.3282.119 Safari/537.36';

    /**
     * The repository owner user/group
     *
     * @var string
     */
    private $owner = '';

    /**
     * The repository name
     *
     * @var string
     */
    private $repo = '';

    /**
     * Github access token
     *
     * @see https://github.com/settings/tokens
     *
     * @var string
     */
    private $token = '';

    /**
     * @return static
     */
    public static function new(): self
    {
        return new self();
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
        $self = clone $this;

        $self->owner = $owner;
        $self->repo  = $repo;

        return $self;
    }

    /**
     * @param string $ownerAndRepo eg: "gookit/color"
     *
     * @return $this
     */
    public function withPathRepo(string $ownerAndRepo): self
    {
        [$owner, $repo] = explode('/', $ownerAndRepo, 2);

        return $this->withOwnerRepo($owner, $repo);
    }

    /**
     * @param int    $issueId
     * @param string $comment
     *
     * @return array
     */
    public function addIssueComment(int $issueId, string $comment): array
    {
        // POST /repos/:owner/:repo/issues/:issue_number/comments
        $url = sprintf(self::ADD_ISSUE_COMMENT, $this->owner, $this->repo, $issueId);

        return $this->sendRequest($url, ['body' => $comment]);
    }

    /**
     * @return HttpClient
     */
    public function newClient(): HttpClient
    {
        $http = new HttpClient();
        $http->setOptions([
            'headers' => [
                // 'Authorization' => 'Basic ' . $this->token,
                'Authorization' => 'Token ' . $this->token,
                'User-Agent'    => self::DEF_USER_AGENT,
            ],
        ]);

        return $http;
    }

    /**
     * @param string $url
     * @param array  $data
     *
     * @return array
     */
    public function sendRequest(string $url, array $data): array
    {
        // curl -u username:token https://api.github.com/user
        // curl -H "Authorization: token OAUTH-TOKEN" https://api.github.com
        $http = $this->newClient();
        $resp = $http->json(self::BASE_API_URL . $url, $data);

        if (!$json = $resp->getBody()->getContents()) {
            return [];
        }

        return JsonHelper::decode($json, true);
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
    public function getOwner(): string
    {
        return $this->owner;
    }

    /**
     * @param string $owner
     */
    public function setOwner(string $owner): void
    {
        $this->owner = $owner;
    }
}
