<?php declare(strict_types=1);
/**
 * This file is part of PTool.
 *
 * @link     https://github.com/inhere
 * @author   https://github.com/inhere
 * @license  MIT
 */

namespace Inhere\PTool\Console\Command;

use Inhere\Console\Command;
use Inhere\Console\IO\Input;
use Inhere\Console\IO\Output;
use Inhere\PTool\Common\CliMarkdown;
use Inhere\PTool\ManDoc\Document;
use Toolkit\Cli\Color;
use function rtrim;

/**
 * Class DemoCommand
 */
class DocCommand extends Command
{
    protected static $name = 'doc';

    protected static $description = 'Provide some useful man docs for git,linux and more commands';

    public static function aliases(): array
    {
        return ['man', 'docs'];
    }

    /**
     * Configure command
     */
    protected function configure(): void
    {
        $def = $this->createDefinition();

        $def->addArgument('top', Input::ARG_OPTIONAL, 'The top document topic name');
        $def->addArgument('subs', Input::ARG_IS_ARRAY, 'The more sub document topic name(s)');

        $def->addOption('list-topic', 'l', Input::OPT_BOOLEAN, 'list all top/sub topics');
    }

    /**
     * @return Document
     */
    private function prepareManDoc(): Document
    {
        $info = $this->app->getParam('manDocs');

        $lang  = $info['lang'] ?? 'en';
        $paths = $info['paths'] ?? [];

        $man = new Document($paths, $lang);
        $man->prepare();

        return $man;
    }

    /**
     * @param Input  $input
     * @param Output $output
     *
     * @example
     *  {fullCmd} -l
     *  {fullCmd} git -l
     *  {fullCmd} git tag
     *  {fullCmd} git branch
     */
    protected function execute($input, $output)
    {
        $man = $this->prepareManDoc();
        if ($errPaths = $man->getErrorPaths()) {
            $output->aList($errPaths, 'error paths');
        }

        if (!$man->getRealPaths()) {
            $output->liteError('paths is empty! please config manDocs.paths');
            return;
        }

        $top  = $input->getStringArg('top', '');
        $subs = $input->getArrayArg('subs', []);

        $nameString = Document::names2string($top, $subs);
        if ($input->getBoolOpt('list-topic')) {
            $topics = $man->getTopicsInfo($top, $subs);

            $output->aList($topics, 'document topics list on the #' . $nameString);
            return;
        }

        if (!$top) {
            $output->error('please input an topic for see document');
            return;
        }

        $topic = $man->findTopic($top, $subs);
        if (!$topic) {
            $output->liteError('The topic is not found! #' . $nameString);
            return;
        }

        if (!$file = $topic->getDocFile()) {
            $output->liteError("not found document for the topic #$nameString");
            return;
        }

        // read content
        $text = $file->getFileContent();

        // parse content
        $md  = new CliMarkdown();
        $doc = $md->parse($text);
        $doc = Color::parseTag(rtrim($doc));

        $output->colored("Document for the #$nameString");
        $output->writeRaw($doc);
    }
}
