<?php declare(strict_types=1);

namespace Inhere\Kite\Model\Dto;

use Toolkit\Stdlib\Obj\AbstractObj;

/**
 * class BranchCommitDto
 *
 * - gen: `kite json dto @c`
 *
 * @author inhere
 * @date 2023/1/6
 */
class BranchCommitDto extends AbstractObj
{
    /**
     * id: 036236671dd08dab0cdabe47cee4f584ac9d4407
     *
     * @var string
     */
    public $id;

    /**
     * short_id: "03623667"
     *
     * @var string
     */
    public $shortId;

    /**
     * title: "Merge branch 'master' into 'fea_220705'"
     *
     * @var string
     */
    public $title;

    /**
     * created_at
     *
     * @var string
     */
    public $createdAt;

    /**
     * parent_ids
     *
     * @var array
     */
    public $parentIds;

    /**
     * message: "Merge branch 'master' into 'fea_220705'\n\n系统自动合并\n\nSee merge request common/yii2-lib!4049",
     *
     * @var string
     */
    public $message;

    /**
     * author_name
     *
     * @var string
     */
    public $authorName;

    /**
     * author_email
     *
     * @var string
     */
    public $authorEmail;

    /**
     * authored_date
     *
     * @var string
     */
    public $authoredDate;

    /**
     * committer_name
     *
     * @var string
     */
    public $committerName;

    /**
     * committer_email
     *
     * @var string
     */
    public $committerEmail;

    /**
     * committed_date
     *
     * @var string
     */
    public $committedDate;

    /**
     * @return bool
     */
    public function isFromMaster(): bool
    {
        return str_contains($this->title, "Merge branch 'master' into ");
    }

}
