<?php declare(strict_types=1);

namespace Inhere\Kite\Common\GitLocal;

/**
 * class GitConst
 *
 * @author inhere
 * @date 2022/10/28
 */
class GitConst
{
    public const GITHUB_HOST = 'https://github.com';
    public const GITHUB_GIT = 'git@github.com';

    /**
     * remote url host type
     */
    public const HOST_TYPE_GH = 'github';
    public const HOST_TYPE_GL = 'gitlab';
    public const HOST_TYPE_LOC = 'gitloc';

    public const ALLOW_HOST_TYPES = [
       self::HOST_TYPE_GH,
       self::HOST_TYPE_GL,
    ];
}
