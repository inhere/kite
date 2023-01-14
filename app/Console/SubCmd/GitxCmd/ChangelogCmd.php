<?php declare(strict_types=1);

namespace Inhere\Kite\Console\SubCmd\GitxCmd;

use Inhere\Console\Command;
use Inhere\Console\IO\Input;
use Inhere\Console\IO\Output;
use Inhere\Kite\Console\Component\Clipboard;
use PhpGit\Changelog\Filter\KeywordsFilter;
use PhpGit\Changelog\Formatter\GithubReleaseFormatter;
use PhpGit\Changelog\Formatter\SimpleFormatter;
use PhpGit\Changelog\GitChangeLog;
use PhpGit\Git;
use PhpGit\Info\TagsInfo;
use PhpGit\Repo;
use Toolkit\Stdlib\Str;

/**
 * class ChangelogCmd
 *
 * @author inhere
 * @date 2023/1/12
 */
class ChangelogCmd extends Command
{
    protected static string $name = 'changelog';
    protected static string $desc = 'collect git change log information by `git log`';

    public static function aliases(): array
    {
        return ['chlog', 'clog', 'cl'];
    }

    /**
     * collect git change log information by `git log`
     *
     * @arguments
     *  oldVersion      string;The old version. eg: v1.0.2
     *                  - keywords `last/latest` will auto use latest tag.
     *                  - keywords `prev/previous` will auto use previous tag.;required
     *  newVersion      string;The new version. eg: v1.2.3
     *                  - keywords `head` will use `Head` commit.;required
     *
     * @options
     *  --exclude               Exclude contains given sub-string. multi by comma split.
     *  --fetch-tags            bool;Update repo tags list by `git fetch --tags`
     *  --file                  Export changelog message to file
     *  --filters               Apply built in log filters. multi by `|` split. TODO
     *                          allow:
     *                          kw     keyword filter. eg: `kw:tom`
     *                          kws    keywords filter.
     *                          ml     msg length filter.
     *                          wl     word length filter.
     *  --format                The git log option `--pretty` value.
     *                          can be one of oneline, short, medium, full, fuller, reference, email,
     *                          raw, format:<string> and tformat:<string>.
     *  -s, --style             The style for generate for changelog.
     *                          allow: markdown(<cyan>default</cyan>), simple, gh-release(ghr)
     *  --repo-url              The git repo URL address. eg: https://github.com/inhere/kite
     *                          default will auto use current git origin remote url
     *  --nm,--no-merges        bool;No contains merge request logs
     *  --us, --unshallow       bool;Convert to a complete warehouse, useful on GitHub Action.
     *  --with-author           bool;Display commit author name
     *  --cb, --to-clipboard    bool;Copy results to clipboard
     *
     * @param Input $input
     * @param Output $output
     *
     * @example
     *   {binWithCmd} last head
     *   {binWithCmd} last head --style gh-release --no-merges
     *   {binWithCmd} v2.0.9 v2.0.10 --no-merges --style gh-release --exclude "cs-fixer,format codes"
     */
    protected function execute(Input $input, Output $output): void
    {
        $fs = $this->flags;
        // see https://devhints.io/git-log-format
        // useful options:
        // --no-merges
        // --glob=<glob-pattern>
        // --exclude=<glob-pattern>

        $repo = Repo::new();
        if ($fs->getOpt('fetch-tags')) {
            $fetch = $repo->newCmd('fetch', '--tags');
            // fix: fetch tags history error on github action.
            // see https://stackoverflow.com/questions/4916492/git-describe-fails-with-fatal-no-names-found-cannot-describe-anything
            $fetch->addIf('--unshallow', $fs->getOpt('unshallow'));
            $fetch->addArgs('--force');
            $fetch->runAndPrint();
        }

        $builder = $repo->newCmd('log');

        // git log v1.0.7...v1.0.8 --pretty=format:'<project>/commit/%H %s' --reverse
        // git log v1.0.7...v1.0.7 --pretty=format:'<li> <a href="https://github.com/inhere/<project>/commit/%H">view commit &bull;</a> %s</li> ' --reverse
        // git log v1.0.7...HEAD --pretty=format:'<li> <a href="https://github.com/inhere/<project>/commit/%H">view commit &bull;</a> %s</li> ' --reverse
        $oldVersion = $fs->getArg('oldVersion');
        $oldVersion = $this->getLogVersion($oldVersion);

        $newVersion = $fs->getArg('newVersion');
        $newVersion = $this->getLogVersion($newVersion);

        $logFmt = GitChangeLog::LOG_FMT_HS;
        if ($fs->getOpt('with-author')) {
            // $logFmt = GitChangeLog::LOG_FMT_HSC;
            $logFmt = GitChangeLog::LOG_FMT_HSA;
        }

        $output->info('collect git log output');
        if ($oldVersion && $newVersion) {
            $builder->add("$oldVersion...$newVersion");
        }

        $builder->addf('--pretty=format:"%s"', $logFmt);

        // $b->addIf("--exclude $exclude", $exclude);
        // $b->addIf('--abbrev-commit', $abbrevID);
        $noMerges = $fs->getOpt('no-merges');
        $builder->addIf('--no-merges', $noMerges);
        $builder->add('--reverse');
        $builder->run();

        $repoUrl = $fs->getOpt('repo-url');
        if (!$repoUrl) {
            $rmtInfo = $repo->getRemoteInfo();
            $repoUrl = $rmtInfo->getHttpUrl();
        }

        $output->info('repo URL: ' . $repoUrl);

        if (!$gitLog = $builder->getOutput()) {
            $output->warning('empty git log output, quit generate');
            return;
        }

        $gcl = GitChangeLog::new($gitLog);
        $gcl->setLogFormat($logFmt);
        $gcl->setRepoUrl($repoUrl);

        if ($exclude = $fs->getOpt('exclude')) {
            $keywords = Str::explode($exclude, ',');
            $gcl->addItemFilter(new KeywordsFilter($keywords));
        }

        $style = $fs->getOpt('style');
        if ($style === 'ghr' || $style === 'gh-release') {
            $gcl->setItemFormatter(new GithubReleaseFormatter());
        } elseif ($style === 'simple') {
            $gcl->setItemFormatter(new SimpleFormatter());
        }

        // parse and generate.
        $output->info('parse logs and generate changelog');
        $gcl->generate();

        $outFile = $fs->getOpt('file');
        $output->info('total collected changelog number: ' . $gcl->getLogCount());

        if ($outFile) {
            $output->info('export changelog to file: ' . $outFile);
            $gcl->export($outFile);
            $output->success('Completed');
        } elseif ($fs->getOpt('to-clipboard')) {
            $output->info('Will send results to clipboard');
            Clipboard::new()->write($gcl->getChangelog());
        } else {
            $output->println($gcl->getChangelog());
        }
    }


    /**
     * @param string $version
     *
     * @return string
     */
    protected function getLogVersion(string $version): string
    {
        $toLower = strtolower($version);
        if ($toLower === 'head') {
            return 'HEAD';
        }

        if ($toLower === 'latest' || $toLower === 'last') {
            $version = $this->getDescSortedTags()->first();
            $this->output->info('auto find latest tag: ' . $version);
        } elseif ($toLower === 'prev' || $toLower === 'previous') {
            $version = $this->getDescSortedTags()->second();
            $this->output->info('auto find previous tag: ' . $version);
        }

        return $version;
    }

    /**
     * @var TagsInfo|null
     */
    private ?TagsInfo $tagsInfo = null;

    /**
     * @return TagsInfo
     */
    protected function getDescSortedTags(): TagsInfo
    {
        if (!$this->tagsInfo) {
            $this->tagsInfo = Git::new()->tag->tagsInfo('-version:refname');
        }

        return $this->tagsInfo;
    }
}
