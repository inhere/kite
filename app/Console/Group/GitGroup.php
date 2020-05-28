<?php declare(strict_types=1);
/**
 * This file is part of Kite.
 *
 * @link     https://github.com/inhere
 * @author   https://github.com/inhere
 * @license  MIT
 */

namespace Inhere\Kite\Console\Group;

use Inhere\Console\Controller;
use Inhere\Console\IO\Input;
use Inhere\Console\IO\Output;
use Inhere\Kite\Common\CmdRunner;
use Inhere\Kite\Helper\AppHelper;
use Inhere\Kite\Helper\GitUtil;
use Toolkit\Cli\Color;
use function sprintf;

/**
 * Class GitGroup
 * - git:tag:push   add tag and push to remote
 * - git:tag:delete delete the tag on remote
 *
 */
class GitGroup extends Controller
{
    protected static $name = 'git';

    protected static $description = 'Some useful tool commands for git flow development';

    public static function aliases(): array
    {
        return ['g'];
    }

    protected static function commandAliases(): array
    {
        return [
            'tag-find' => 'tagFind',
            'tag:find' => 'tagFind',
            'tagfind'  => 'tagFind',
            'tagpush'  => 'tagPush',
            'tag-push' => 'tagPush',
            'tag:push' => 'tagPush',
            'tag:del'  => 'tagDelete',
        ];
    }

    /**
     * get the latest/next git tag from the project directory
     *
     * @options
     * -d, --dir      The project directory path. default is current directory.
     * --next-tag     Display the project next tag version. eg: v2.0.2 => v2.0.3
     * --only-tag     Only output tag information
     *
     * @param Input  $input
     * @param Output $output
     *
     * @example
     *   {fullCmd}
     *   {fullCmd} --only-tag
     *   {fullCmd} -d ../view --next-tag
     *   {fullCmd} -d ../view --next-tag --only-tag
     *
     */
    public function tagFindCommand(Input $input, Output $output): void
    {
        $dir = $input->getOpt('dir', $input->getOpt('d'));
        $dir = $dir ?: $input->getPwd();

        $onlyTag = $input->getBoolOpt('only-tag');
        $nextTag = $input->getBoolOpt('next-tag');

        $tagName = GitUtil::findTag($dir, !$onlyTag);
        if (!$tagName) {
            $output->error('No any tags of the project');
            return;
        }

        $title = 'The latest tag: %s';

        if ($nextTag) {
            $title = "The next tag: %s (current: {$tagName})";
            $tagName = $this->buildNextTag($tagName);
        }

        if ($onlyTag) {
            echo $tagName;
            return;
        }

        Color::println("<info>$title</info> $tagName");
    }

    /**
     * Get next tag version. eg: v2.0.3 => v2.0.4
     *
     * @param string $tagName
     *
     * @return string
     */
    private function buildNextTag(string $tagName): string
    {
        $nodes = explode('.', $tagName);

        $lastNum = array_pop($nodes);
        $nodes[] = (int)$lastNum + 1;

        return implode('.', $nodes);
    }

    /**
     * list all git tags
     *
     * @param Input  $input
     * @param Output $output
     */
    public function tagListCommand(Input $input, Output $output): void
    {
        $output->info('TODO');
    }

    /**
     * Add new tag version and push to the remote git repos
     *
     * @options
     *  -v, --version       *The new tag version. e.g: v2.0.4
     *  -m, --message       The message for add new tag.
     *
     * @param Input  $input
     * @param Output $output
     */
    public function tagPushCommand(Input $input, Output $output): void
    {
        $tag = $input->getSameOpt(['v', 'tag'], '');
        if (!$tag) {
            $output->error('please input new tag version, like: v2.0.4');
            return;
        }

        if (!AppHelper::isVersion($tag)) {
            $output->error('please input an valid tag version, like: v2.0.4');
            return;
        }

        $output->aList([
            'Work Dir' => $input->getPwd(),
            'New Tag'  => $input->getSameOpt(['v', 'tag']),
        ], 'Information');

        $msg = $input->getSameArg(['m', 'message']);
        $msg = $msg ?: "Release $tag";

        // git tag -a $1 -m "Release $1"
        // git push origin --tags
        $cmd = sprintf('git tag -a %s -m "Release %s"; git push origin %s', $tag, $msg, $tag);
        CmdRunner::new($cmd)->do(true);

        $output->success('Complete');
    }

    /**
     * delete an remote tag by git
     *
     * @usage
     *  {command} [-S HOST:PORT]
     *  {command} [-H HOST] [-p PORT]
     *
     * @options
     *  -r, --remote The remote name. default <comment>origin</comment>
     *  -v, --tag    The tag version. eg: v2.0.3
     *
     * @param Input  $input
     * @param Output $output
     */
    public function tagDeleteCommand(Input $input, Output $output): void
    {
        $remote = $input->getSameOpt(['r', 'remote'], 'origin');
        $tag    = $input->getSameOpt(['v', 'tag']);

        GitUtil::delRemoteTag($remote, $tag);

        $output->info('Complete');
    }

    /**
     * run git add/commit/push at once command
     *
     * @options
     *  -m, --message The commit message
     *
     * @param Input  $input
     * @param Output $output
     */
    public function ampCommand(Input $input, Output $output): void
    {
        $message = $input->getSameOpt(['m', 'message'], '');
        if (!$message) {
            $output->liteError('please input an message for git commit');
            return;
        }

        $output->info('Work Dir: ' . $input->getPwd());

        $run = CmdRunner::new('git add .')->do(true);

        $run->okDoRun(sprintf('git commit -m "%s"', $message));
        $run->okDoRun('git push');

        $output->info('Complete');
    }
}
