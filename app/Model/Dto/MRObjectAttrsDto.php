<?php declare(strict_types=1);

namespace Inhere\Kite\Model\Dto;

use Toolkit\Stdlib\Obj\AbstractObj;

/**
 * class MRObjectAttrs `$.object_attributes`
 *
 * @author inhere
 */
class MRObjectAttrsDto extends AbstractObj
{
    /**
     * assignee_id
     *
     * @var string
     */
    public $assigneeId;

    /**
     * author_id
     *
     * @var integer
     */
    public $authorId = 0;

    /**
     * created_at
     *
     * @var string
     */
    public $createdAt;

    /**
     * description
     *
     * @var string
     */
    public $description;

    /**
     * head_pipeline_id
     *
     * @var string
     */
    public $headPipelineId;

    /**
     * id
     *
     * @var integer
     */
    public $id = 0;

    /**
     * iid
     *
     * @var integer
     */
    public $iid = 0;

    /**
     * last_edited_at
     *
     * @var string
     */
    public $lastEditedAt;

    /**
     * last_edited_by_id
     *
     * @var string
     */
    public $lastEditedById;

    /**
     * merge_commit_sha
     *
     * @var string
     */
    public $mergeCommitSha;

    /**
     * merge_error
     *
     * @var string
     */
    public $mergeError;

    /**
     * merge_params
     *
     * @var object
     */
    public $mergeParams;

    /**
     * merge_status: unchecked, can_be_merged
     *
     * @var string
     */
    public $mergeStatus = '';

    /**
     * merge_user_id
     *
     * @var int
     */
    public $mergeUserId;

    /**
     * merge_when_pipeline_succeeds
     *
     * @var boolean
     */
    public $mergeWhenPipelineSucceeds;

    /**
     * milestone_id
     *
     * @var string
     */
    public $milestoneId;

    /**
     * source_branch eg: fea_230103
     *
     * @var string
     */
    public $sourceBranch = '';

    /**
     * source_project_id
     *
     * @var integer
     */
    public $sourceProjectId = 0;

    /**
     * state: opened, merged
     *
     * @var string
     */
    public $state = '';

    /**
     * target_branch eg: master
     *
     * @var string
     */
    public $targetBranch = '';

    /**
     * target_project_id
     *
     * @var integer
     */
    public $targetProjectId = 0;

    /**
     * time_estimate
     *
     * @var integer
     */
    public $timeEstimate;

    /**
     * title
     *
     * @var string
     */
    public $title;

    /**
     * updated_at
     *
     * @var string
     */
    public $updatedAt;

    /**
     * url
     *
     * @var string
     */
    public $url;

    /**
     * source
     *
     * @var object
     */
    public $source;

    /**
     * target
     *
     * @var object
     */
    public $target;

    /**
     * last_commit
     *
     * @var object
     */
    public $lastCommit;

    /**
     * work_in_progress
     *
     * @var boolean
     */
    public $workInProgress;

    /**
     * total_time_spent
     *
     * @var integer
     */
    public $totalTimeSpent;

    /**
     * action
     *
     * @var string
     */
    public $action;

}
