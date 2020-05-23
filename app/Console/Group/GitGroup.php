<?php
/**
 * Created by PhpStorm.
 * User: inhere
 * Date: 2017-10-18
 * Time: 18:58
 */

namespace Inhere\PTool\Console\Group;

use Inhere\Console\Controller;
use Inhere\Console\IO\Input;
use Inhere\Console\IO\Output;
use Inhere\PTool\Helper\GitHelper;
use Toolkit\Cli\Color;

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
        return ['gf'];
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

        $tagName = GitHelper::findTag($dir, !$onlyTag);
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
     * run a php built-in server for development(is alias of the command 'server:dev')
     *
     * @usage
     *  {command} [-S HOST:PORT]
     *  {command} [-H HOST] [-p PORT]
     * @options
     *  -S         The server address. e.g 127.0.0.1:5577
     *  -H,--host  The server host address. e.g 127.0.0.1
     *  -p,--port  The server host address. e.g 5577
     *
     * @param Input  $input
     * @param Output $output
     */
    public function tagDeleteCommand(Input $input, Output $output): void
    {
        echo "string\n";
    }
}
