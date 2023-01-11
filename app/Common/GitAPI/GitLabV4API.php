<?php declare(strict_types=1);

namespace Inhere\Kite\Common\GitAPI;

use PhpPkg\Http\Client\AbstractClient;
use function array_filter;

/**
 * class GitLabV4API
 */
class GitLabV4API extends AbstractGitAPI
{
    /*
     * response headers for query list.
     */

    // X-Page: 1
    public const RESP_HEADER_PAGE = 'X-Page';

    // X-Next-Page: 2
    public const RESP_HEADER_NEXT_PAGE = 'X-Next-Page';

    // X-Total: 593
    public const RESP_HEADER_TOTAL = 'X-Total';

    // X-Total-Pages: 30
    public const RESP_HEADER_TOTAL_PAGE = 'X-Total-Pages';

    /**
     * @param string $projectId
     * @param bool $withStat
     *
     * @return array
     */
    public function getProject(string $projectId, bool $withStat = false): array
    {
        $uri = "/projects/$projectId";
        if ($withStat) {
            $uri .= '?statistics=true';
        }

        return $this->sendRequest('GET', $uri);
    }

    /**
     * @param string $projectId
     * @param int $mergeId
     *
     * @return array
     */
    public function mergePullRequest(string $projectId, int $mergeId): array
    {
        $apiUri = "/projects/$projectId/merge_requests/$mergeId/merge";

        return $this->sendRequest('PUT', $apiUri);
    }

    /**
     * 创建pull request
     *
     * @param string $projectId
     * @param string $source
     * @param string $target
     * @param string $title
     *
     * @return array
     */
    public function createPullRequest(string $projectId, string $source, string $target, string $title): array
    {
        return $this->sendRequest('POST', "/projects/$projectId/merge_requests/", [
            'source_branch' => $source,
            'target_branch' => $target,
            'title'         => $title
        ]);
    }

    /**
     * 查询两个分支是否存在未合并的pull request
     *
     * @param string $projectId
     * @param string $source
     * @param string $target
     *
     * @return array
     */
    public function getOnePRByBranches(string $projectId, string $source, string $target): array
    {
        $merges = $this->sendRequest('GET', '/projects/' . $projectId . '/merge_requests/', [
            'source_branch' => $source,
            'target_branch' => $target,
            'state'         => 'opened'
        ]);

        return $merges[0] ?? [];
    }

    /**
     * 对比两个分支代码
     *
     * @param string $projectId
     * @param string $source
     * @param string $target
     *
     * @return array = ['commit' => [], 'commits' => [], 'diffs' => []]
     * @see http://gitlab.gongzl.com/help/api/repository.md
     */
    public function compareBranches(string $projectId, string $source, string $target): array
    {
        return $this->sendRequest('GET', "/projects/$projectId/repository/compare/", [
            'from' => $target,
            'to'   => $source,
        ]);
    }

    /**
     * 获取所有分支
     * - http://gitlab.gongzl.com/help/api/branches.md#list-repository-branches
     *
     * @param string $projectId
     * @param string $search keywords
     * @param array $params = [
     *     'page'     => 1,
     *     'per_page' => 20,
     * ]
     *
     * @return array
     */
    public function getBranches(string $projectId, string $search = '', array $params = []): array
    {
        $uri = "/projects/$projectId/repository/branches";

        $params['search'] = $search;

        $cli = $this->sendThen('GET', $uri, array_filter($params));
        $ret = $this->getPageInfo($cli);

        $data = $cli->getJsonArray();
        // set data
        if (isset($data['error'])) {
            $ret['errCode'] = $cli->getStatusCode();
            $ret['error'] = $data['error'];
        } else {
            $ret['list'] = $data;
        }

        return $ret;
    }

    private function getPageInfo(AbstractClient $cli): array
    {
        return [
            'list'      => [],
            'page'      => (int)$cli->getResponseHeader(self::RESP_HEADER_PAGE, '0'),
            'nextPage'  => (int)$cli->getResponseHeader(self::RESP_HEADER_NEXT_PAGE, '0'),
            'totalPage' => (int)$cli->getResponseHeader(self::RESP_HEADER_TOTAL_PAGE, '0'),
            'totalNum'  => (int)$cli->getResponseHeader(self::RESP_HEADER_TOTAL, '0'),
        ];
    }

    /**
     * 获取分支信息
     *
     * @param string $projectId
     * @param string $branch
     *
     * @return array
     */
    public function getBranch(string $projectId, string $branch): array
    {
        return $this->sendRequest('GET', "/projects/$projectId/repository/branches/$branch");
    }

    /**
     * delete-repository-branch
     * - docs http://gitlab.gongzl.com/help/api/branches.md#delete-repository-branch
     *
     * @param string $projectId
     * @param string $branch
     *
     * @return array = ['message' => '', 'errCode' => 404]
     */
    public function delBranch(string $projectId, string $branch): array
    {
        // DELETE /projects/:id/repository/branches/:branch
        return $this->sendRequest('DELETE', "/projects/$projectId/repository/branches/$branch");
    }

    /**
     * 获取 tag 列表(每次返回20个)
     * - http://gitlab.gongzl.com/help/api/tags.md#list-project-repository-tags
     *
     * @param string $projectId
     * @param array $params = [
     *     'page'     => 1,
     *     'per_page' => 20,
     *     'order_by' => 'updated', // ordered by name or updated fields
     *     'sort'     => 'desc', // sorted in asc or desc order
     * ]
     *
     * @return array
     */
    public function getTags(string $projectId, array $params = []): array
    {
        $uri = "/projects/$projectId/repository/tags";
        $cli = $this->sendThen('GET', $uri, array_filter($params));

        $ret = $this->getPageInfo($cli);
        // set data
        $ret['list'] = $cli->getJsonArray();

        return $ret;
    }

    /**
     * Delete a tag
     *
     * @param string $projectId
     * @param string $tag
     *
     * @return array = ['message' => '', 'errCode' => 404]
     */
    public function delTag(string $projectId, string $tag): array
    {
        // DELETE /projects/:id/repository/tags/:tag_name
        return $this->sendRequest('DELETE', "/projects/$projectId/repository/tags/$tag");
    }

    /**
     * GET /groups/:id/members
     *
     * @param int|string $groupId
     *
     * @return array
     */
    public function getGroupMembers(int|string $groupId): array
    {
        return [];
    }

    /**
     * GET /projects/:id/members
     *
     * @param int|string $projectId
     *
     * @return array
     */
    public function getProjectMembers(int|string $projectId): array
    {
        return [];
    }
}
