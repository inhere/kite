<?php declare(strict_types=1);

namespace Inhere\Kite\Common\GitAPI;

/**
 * class GitLabV4API
 */
class GitLabV4API extends AbstractGitAPI
{
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
