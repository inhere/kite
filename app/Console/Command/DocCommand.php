<?php declare(strict_types=1);
/**
 * This file is part of Kite.
 *
 * @link     https://github.com/inhere
 * @author   https://github.com/inhere
 * @license  MIT
 */

namespace Inhere\Kite\Console\Command;

use Inhere\Console\Command;
use Inhere\Console\IO\Input;
use Inhere\Console\IO\Output;
use Inhere\Console\Util\Helper;
use Inhere\Kite\Common\CliMarkdown;
use Inhere\Kite\Helper\AppHelper;
use Inhere\Kite\ManDoc\DocTopic;
use Inhere\Kite\ManDoc\Document;
use Toolkit\Cli\Color;
use Toolkit\Sys\Proc\ProcWrapper;
use function array_pop;
use function dirname;
use function implode;
use function rtrim;
use function str_replace;

/**
 * Class DemoCommand
 *
 * @link https://github.com/jaywcjlove/linux-command/tree/master/command  linux commands zh-CN documents
 *       - raw contents eg: https://raw.githubusercontent.com/jaywcjlove/linux-command/master/command/accept.md
 */
class DocCommand extends Command
{
    protected static $name = 'doc';

    protected static $description = 'Useful documents for how to use git,tmux and more tool';

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

        $def->addOption('lang', '', Input::OPT_OPTIONAL, 'use the language for find topic document',
            Document::DEF_LANG);
        $def->addOption('create', '', Input::OPT_BOOLEAN, 'create an new topic document');
        $def->addOption('cat', '', Input::OPT_BOOLEAN, 'see the document file contents');
        $def->addOption('edit', 'e', Input::OPT_BOOLEAN, 'edit an topic document');
        $def->addOption('editor', '', Input::OPT_OPTIONAL, 'editor for edit the topic document', 'vim');
        $def->addOption('list-topic', 'l', Input::OPT_BOOLEAN, 'list all top/sub topics');

        $example = <<<TXT
{binWithCmd} -l   List all top topics
  {binWithCmd} git -l   List all topics on the #git
  {binWithCmd} git tag
  {binWithCmd} git branch
  {binWithCmd} tmux --lang zh-CN
TXT;

        // alone running
        if (!$this->isAttached()) {
            $example = str_replace('binWithCmd', 'binName', $example);
        }

        $def->setExample($example);
    }

    /**
     * @return Document
     */
    private function prepareManDoc(): Document
    {
        $info = $this->app->getParam('manDocs');

        $paths = $info['paths'] ?? [];
        $lang  = $this->input->getStringOpt('lang');
        if (!$lang) {
            $lang = $info['lang'] ?? AppHelper::getLangFromENV(Document::DEF_LANG);
        }

        $man = new Document($paths, $lang);
        $man->prepare();

        return $man;
    }

    /**
     * @param Input  $input
     * @param Output $output
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
            if ($input->getBoolOpt('create')) {
                $this->createTopic($man, $output, $nameString);
                return;
            }

            $output->error('The topic is not found! #' . $nameString);
            return;
        }

        if (!$file = $topic->getDocFile()) {
            $output->liteError("not found document for the topic #$nameString");
            return;
        }

        // edit document
        if ($input->getSameOpt(['e', 'edit'], false)) {
            $this->editTopic($file);
            return;
        }

        // read content
        $text = $file->getFileContent();
        if ($input->getBoolOpt('cat')) {
            $output->writeRaw($text);
            return;
        }

        // parse content
        $md  = new CliMarkdown($man->getLang());
        $doc = $md->parse($text);
        $doc = Color::parseTag(rtrim($doc));

        // $output->colored("Document for the #$nameString");
        $output->writeRaw($doc);
    }

    /**
     * @param Document $doc
     * @param Output   $output
     */
    private function createTopic(Document $doc, Output $output, string $nameString): void
    {
        $path = $this->topName;
        if ($this->subNames) {
            $path .= '/' . implode('/', $this->subNames);
        }

        $realPaths = $doc->getRealPaths();
        $lastPath  = array_pop($realPaths);
        $filepath  = $lastPath . '/' . $path . Document::EXT;

        $output->info('will create document #' . $nameString);
        $output->aList([
            'topic' => $nameString,
            'file'  => $filepath,
        ], 'document info', ['ucFirst' => false]);

        Helper::mkdir(dirname($filepath), 0755);

        $editor = $this->input->getStringOpt('editor', 'vim');
        ProcWrapper::runEditor($editor, $filepath);

        $output->success('new document is created');
    }

    /**
     * @param DocTopic $topic
     */
    private function editTopic(DocTopic $topic): void
    {
        $filepath = $topic->getPath();
        $editor   = $this->input->getStringOpt('editor', 'vim');

        $this->output->title("will use '{$editor}' for edit the document");

        ProcWrapper::runEditor($editor, $filepath);

        $this->output->success('the document is changed');
    }

    /**
     * @param Document $man
     * @param string   $nameString
     */
    private function listTopicInfo(Document $man, string $nameString): void
    {
        if (!$this->topName) {
            $info = $man->getTopicsInfo();
            $this->output->aList($info, 'All top-level topics list');
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

        // is topic dir.
        $topics = $topic->getChildsInfo();
        $default = $topic->getDefault();

        $info   = [
            'metadata' => [
                'name' => $topic->getName(),
                'node' => '#' . $nameString,
                'file' => $default ? $default->getPath() : 'No default file',
            ],
            'topics'   => $topics,
        ];

        $this->output->title('topics information for #' . $nameString, [
            'indent' => 0,
        ]);
        $this->output->mList($info, [
            'ucFirst' => false,
        ]);
    }
}
