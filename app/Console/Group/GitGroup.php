<?php declare(strict_types=1);
/**
 * This file is part of PTool.
 *
 * @link     https://github.com/inhere
 * @author   https://github.com/inhere
 * @license  MIT
 */

namespace Inhere\PTool\Console\Group;

use Inhere\Console\Controller;
use Inhere\Console\IO\Input;
use Inhere\Console\IO\Output;
use Inhere\PTool\Helper\GitUtil;
use Inhere\PTool\Helper\SysCmd;
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
            'tag-find'  => 'tagFind',
            'tag:find'  => 'tagFind',
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
            Color::println('No any tags of the project', 'error');
            return;
        }

        $title = 'The latest tag: %s';

        if ($nextTag) {
            $title = "The next tag: %s (current: {$tagName})";
            $nodes = explode('.', $tagName);

            $lastNum = array_pop($nodes);
            $nodes[] = (int)$lastNum + 1;
            $tagName = implode('.', $nodes);
        }

        if ($onlyTag) {
            echo $tagName;
            return;
        }

        Color::printf("<info>$title</info> $tagName\n");
    }

    /**
     * Get next tag version. eg: v2.0.3 => v2.0.4
     *
     * @param string $tagName
     *
     * @return string
     */
    public function buildNextTag(string $tagName): string
    {
        $nodes = explode('.', $tagName);

        $lastNum = array_pop($nodes);
        $nodes[] = (int)$lastNum + 1;

        return implode('.', $nodes);
    }

    /**
     * Add new tag version and push to the remote git repos
     *
     * @usage
     *  {command} [-S HOST:PORT]
     *  {command} [-H HOST] [-p PORT]
     * @options
     *  -v         The new version. e.g: v2.0.4
     *
     * @param Input  $input
     * @param Output $output
     */
    public function tagPushCommand(Input $input, Output $output): void
    {
        echo "string ddd\n";
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
        $tag = $input->getSameOpt(['v', 'tag']);

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
            $output->liteError('please input commit message');
            return;
        }

        $output->info('Work Dir: ' . $input->getPwd());
        SysCmd::exec('git add .');

        SysCmd::exec(sprintf('git commit -m "%s"', $message));

        SysCmd::exec('git push');

        $output->info('Complete');
    }
}
