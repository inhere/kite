<?php declare(strict_types=1);

namespace Inhere\Kite\Model\Dto;

use Toolkit\Stdlib\Obj\AbstractObj;

/**
 * class GitlabRepositoryDto
 *
 * @author inhere
 * @date 2023/1/6
 */
class GitlabRepositoryDto extends AbstractObj
{
    /**
     * name: "yii2-stats"
     *
     * @var string
     */
    public $name = '';

    /**
     * url
     *
     * @var string
     */
    public $url = '';

    /**
     * description
     *
     * @var string
     */
    public $description = '';

    /**
     * homepage
     *
     * @var string
     */
    public $homepage = '';

    /**
     * git_http_url
     *
     * @var string
     */
    public $gitHttpUrl = '';

    /**
     * git ssh url
     *
     * @var string
     */
    public $gitSshUrl = '';

    /**
     * visibility level
     *
     * @var integer
     */
    public $visibilityLevel;
}
