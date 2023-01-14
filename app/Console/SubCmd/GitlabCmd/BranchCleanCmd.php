<?php declare(strict_types=1);

namespace Inhere\Kite\Console\SubCmd\GitlabCmd;

use Inhere\Console\Command;
use Inhere\Console\IO\Input;
use Inhere\Console\IO\Output;
use Inhere\Kite\Common\GitAPI\GitLabV4API;
use Inhere\Kite\Kite;
use Toolkit\PFlag\FlagsParser;
use function urlencode;

/**
 * class BranchCleanCmd
 *
 * @author inhere
 * @date 2023/1/9
 */
class BranchCleanCmd extends Command
{
    protected static string $name = 'clean';
    protected static string $desc = 'quickly clean git remote branches by input conditions';

    public static function aliases(): array
    {
        return ['clear', 'clr'];
    }

    protected function configFlags(FlagsParser $fs): void
    {
        $fs->addOptsByRules([
            's, search' => 'search branch name for delete',
            'r, remote' => 'git remote name for get repo path',
        ]);
        $fs->addArgByRule('repoPath', 'the repo path with group name. eg: group/name');
    }

    /**
     * @param Input  $input
     * @param Output $output
     *
     */
    protected function execute(Input $input, Output $output): void
    {
        $fs = $this->flags;

        $keywords = $fs->getOpt('search');
        $repoPath = $fs->getArg('repoPath');
        $projectId = urlencode($repoPath);

        /** @var GitLabV4API $glApi */
        $glApi = Kite::get('glApi');
        $brData = $glApi->getBranches($projectId, $keywords);

        foreach ($brData['list'] as $item) {
            $output->println("branch: {$item['name']}");
        }
    }
}
