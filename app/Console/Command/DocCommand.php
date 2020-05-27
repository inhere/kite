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

    /**
     * @var string
     */
    private $topName = '';

    /**
     * @var array
     */
    private $subNames = [];

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

        $this->topName  = $input->getStringArg('top', '');
        $this->subNames = $input->getArrayArg('subs', []);

        $nameString = Document::names2string($this->topName, $this->subNames);

        if ($input->getBoolOpt('list-topic')) {
            $this->listTopicInfo($man, $nameString);
            return;
        }

        if (!$this->topName) {
            $output->error('please input an topic name for see document');
            return;
        }

        $topic = $man->findTopic($this->topName, $this->subNames);
        if (!$topic) {
            $output->error('The topic is not found! #' . $nameString);
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

    private function listTopicInfo(Document $man, string $nameString): void
    {
        if (!$this->topName) {
            $info = $man->getTopicsInfo();
            $this->output->aList($info, 'document topics list on the #' . $nameString);
            return;
        }

        $topic = $man->findTopic($this->topName, $this->subNames);
        if (!$topic) {
            $this->output->error('The topic is not found! #' . $nameString);
            return;
        }

        // is doc file
        if ($topic->isFile()) {
            $info = [
                'name' => $topic->getName(),
                'node' => '#' . $nameString,
                'file' => $topic->getPath(),
            ];

            $this->output->aList($info, "topic information for #$nameString", [
                'ucFirst' => false,
            ]);
            return;
        }

        $topics = $topic->getChildsInfo();
        $info = [
            'metadata' => [
                'name' => $topic->getName(),
                'node' => '#' . $nameString,
                'file' => $topic->getPath(),
            ],
            'topics' => $topics,
        ];

        $this->output->title('topics information for #' . $nameString, [
            'indent' => 0,
        ]);
        $this->output->mList($info, [
            'ucFirst' => false,
        ]);
    }
}
