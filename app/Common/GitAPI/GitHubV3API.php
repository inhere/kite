<?php declare(strict_types=1);

namespace Inhere\Kite\Common\GitAPI;

use function sprintf;

/**
 * Class GitHubV3API
 */
class GitHubV3API extends AbstractGitAPI
{
    public const BASE_API_URL = 'https://api.github.com';

    public const BASE_REPOS_URL = 'https://api.github.com/repos';

    // POST /repos/:owner/:repo/issues/:issue_number/comments
    public const ADD_ISSUE_COMMENT = '/repos/%s/%s/issues/%d/comments';

    /**
     * @var string
     */
    protected string $baseApi = self::BASE_API_URL;

    /**
     * @param int    $issueId
     * @param string $comment
     *
     * @return array
     */
    public function addIssueComment(int $issueId, string $comment): array
    {
        // POST /repos/:owner/:repo/issues/:issue_number/comments
        $url = sprintf(self::ADD_ISSUE_COMMENT, $this->group, $this->repo, $issueId);

        return $this->sendPOST($url, ['body' => $comment]);
    }
}
