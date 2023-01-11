<?php declare(strict_types=1);

namespace Inhere\Kite\Model\Dto;

use Toolkit\Stdlib\Obj\AbstractObj;

/**
 * class GitlabProjectDTO `$.project`
 *
 * @author inhere
 */
class GitlabProjectDto extends AbstractObj
{
    /**
     * id
     *
     * @var integer
     */
    public $id;

    /**
     * name
     *
     * @var string
     */
    public $name;

    /**
     * description
     *
     * @var string
     */
    public $description;

    /**
     * web_url
     *
     * @var string
     */
    public $webUrl;

    /**
     * git_ssh_url
     *
     * @var string
     */
    public $gitSshUrl;

    /**
     * git_http_url
     *
     * @var string
     */
    public $gitHttpUrl;

    /**
     * namespace
     *
     * @var string
     */
    public $namespace;

    /**
     * visibility_level
     *
     * @var integer
     */
    public $visibilityLevel;

    /**
     * path_with_namespace
     * eg: "common/yii2-lib"
     *
     * @var string
     */
    public $pathWithNamespace;

    /**
     * default_branch
     *
     * @var string
     */
    public $defaultBranch;

    /**
     * homepage
     *
     * @var string
     */
    public $homepage;

    /**
     * url
     *
     * @var string
     */
    public $url;

    /**
     * ssh_url
     *
     * @var string
     */
    public $sshUrl;

    /**
     * http_url
     *
     * @var string
     */
    public $httpUrl;

}
