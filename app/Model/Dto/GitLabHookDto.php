<?php declare(strict_types=1);

namespace Inhere\Kite\Model\Dto;


use Toolkit\Stdlib\Obj\AbstractObj;

/**
 * class GitLabHookDTO
 */
class GitLabHookDto extends AbstractObj
{
    /**
     * @var string eg: "push"
     */
    public $eventName = '';

    /**
     * 创建/合并mr时 "event_type": "merge_request"
     * - 并且没有 eventName
     *
     * @var string
     */
    public $eventType = '';

    /**
     * @var string eg: "push"
     */
    public $objectKind = '';

    /**
     * project_id
     *
     * @var int
     */
    public $projectId = 0;

    /**
     * @var string "df1fa91891413020a0b5de25dfcef3c772ed60f8"
     */
    public $before = '';

    /**
     * @var string "6fef1bc23a85e245ae587c981a599bad16185f15"
     */
    public $after = '';

    /**
     * @var string eg: "refs/heads/master"
     */
    public $ref = '';

    /**
     * total_commits_count for MR.
     *
     * @var string
     */
    public $totalCommitsCount = 0;

    /**
     * @var array
     */
    public $project = [];

    /**
     * @var array
     */
    public $repository = [];

    /**
     * @var array[]
     */
    public $commits = [];

    /**
     * @var GitlabProjectDto
     */
    private $projectDto;

    /**
     * @var GitlabRepositoryDto
     */
    private $repositoryDto;

    /**
     * @return GitlabProjectDto
     */
    public function getProjectDto(): GitlabProjectDto
    {
        if (!$this->projectDto) {
            $this->projectDto = GitlabProjectDto::new($this->project);
        }

        return $this->projectDto;
    }

    /**
     * @return GitlabRepositoryDto
     */
    public function getRepositoryDto(): GitlabRepositoryDto
    {
        if (!$this->repositoryDto) {
            $this->repositoryDto = GitlabRepositoryDto::new($this->repository);
        }

        return $this->repositoryDto;
    }
}
