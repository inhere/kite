<?php declare(strict_types=1);

namespace Inhere\Kite\Common\GitLocal;

use Inhere\Kite\Kite;
use PhpGit\Repo;

/**
 * class Gitflow
 *
 * @author inhere
 */
class GitFactory
{
    /**
     * @param string $repoDir
     *
     * @return AbstractGitLocal
     */
    public static function make(string $repoDir = ''): AbstractGitLocal
    {
        $repo = Repo::new($repoDir);

        $platform  = $repo->getPlatform();
        $configKey = $platform !== Repo::PLATFORM_CUSTOM ? $platform : 'git';
        $settings  = Kite::config()->getArray($configKey);

        $typGit = match ($platform) {
            Repo::PLATFORM_GITHUB => new GitHub(null, $settings),
            Repo::PLATFORM_GITLAB => new GitLab(null, $settings),
            default => new GitLoc(null, $settings),
        };

        $typGit->setRepo($repo);
        return $typGit;
    }
}
