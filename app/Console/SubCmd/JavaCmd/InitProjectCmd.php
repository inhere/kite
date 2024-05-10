<?php declare(strict_types=1);

namespace Inhere\Kite\Console\SubCmd\JavaCmd;

use Inhere\Console\Command;
use Inhere\Console\IO\Input;
use Inhere\Console\IO\Output;
use Inhere\Kite\Kite;
use PhpPkg\Config\ConfigBox;
use PhpPkg\Config\ConfigUtil;
use PhpPkg\EasyTpl\EasyTemplate;
use Toolkit\Cli\Cli;
use Toolkit\Extlib\Exec\CmdRunner;
use Toolkit\FsUtil\Extra\FileTreeBuilder;
use Toolkit\FsUtil\FS;
use Toolkit\PFlag\FlagsParser;
use Toolkit\Stdlib\Arr;
use Toolkit\Stdlib\Str;
use function array_merge;
use function basename;
use function explode;
use function implode;

/**
 * class InitJavProjectCmd
 *
 * @author inhere
 */
class InitProjectCmd extends Command
{
    protected static string $name = 'init-repo';
    protected static string $desc = 'quick create and init a java service, bff, gateway project';

    public static function aliases(): array
    {
        return ['ijr', 'ijp', 'init'];
    }

    protected function configFlags(FlagsParser $fs): void
    {
        $fs->addOptByRule('type,t', 'Project type name. allow: service, bff, gateway, common');
        $fs->addOptsByRules([
            'only-base'         => 'bool;not generate dir and files, only copy base files',
            'old'               => 'bool;contains old datasource service, if set will copy xxx-old.properties',
            'v,var,vars'        => 'array;Custom add some init vars, allow multi.
format KEY=VALUE.
 eg: -v port=8089 -v author=my-name',
            'dry,dry-run'       => 'bool;dry run workflows',
            'branches, brs, br' => 'init some branches after init, multi by comma',
            'add-git, ag'       => 'bool;run git commands for add, commit, push after init',
            'w,workdir'         => 'set workdir for init project, default: current dir',
            'c,config'          => 'set the config.php file path for config the command',
        ]);
        $fs->addOptByRule('b, boot, boot-file', 'the bootstrap script PHP file for init project');
        $fs->setHelp('
 Usage:
   $ git clone your-git.com/group/repo repo
   $ cd repo
   $ kitep dev java init -t common --boot script/create-java-project.php

Workflow:
1. init project files and dirs
2. init input branches
3. push to remote repository
        ');
    }

    /**
     * @param Input  $input
     * @param Output $output
     *
     * @return mixed
     */
    protected function execute(Input $input, Output $output): int
    {
        $fs = $this->flags;
        $type = $fs->getOpt('type');

        if ($workDir = $fs->getOpt('workdir')) {
            $workDir = FS::realpath($workDir);
        } else {
            $workDir = $input->getWorkDir();
        }

        // project name. eg: myorg-service-order-query
        $project = basename($workDir);
        if (!$type) {
            $type = $this->getTypeFromName($project);
        }

        $cfg = Kite::config()->getSub('cmd_java_init_repo');
        $tplDir = $cfg->get('tpl_dir') . "/java-$type";
        $nodes  = explode('-', $project);
        $dryRun = $fs->getOpt('dry-run');

        // a.b.c -> a/b/c
        $namePath   = Str::join($nodes, '/');
        $camelName  = Str::toCamel($project, true);
        $baseTplDir = $cfg->get('base_tpl_dir');

        $pv = $cfg->getArray('pom_pkg');

        $product = $nodes[0];
        $tplVars = [
            'type'         => $type,
            'port'         => 8080,
            'company'      => 'COMPANY_NAME',
            'projectName'  => $project,
            'productName'  => $product,
            'camelName'    => $camelName,
            // eg 'MyorgServiceOrderQueryApplication'
            'appClassName' => $camelName . 'Application',
            // eg: myorg-service-order-query -> service-order-query
            'noPfxName'    => implode('-', Arr::deleteKeys($nodes, 0)),
            'namePath'     => $namePath,
            'projectPkg'   => Str::join($nodes, '.'),
            'projectDesc'  => Str::join($nodes, ' '),
            'baseTplDir'   => $baseTplDir,
            // 'pkgVersion'   => Str::splitKvMap($verText, "\n", ' '),
            'pkgVersion'   => $pv,
            'nacosNps'     => [],
        ];

        if ($cfgVars = $cfg->getArray('tpl_vars')) {
            $tplVars = array_merge($tplVars, $cfgVars);
        }
        if ($tplVarFile = $cfg->get('tpl_var_file')) {
            $output->info('read tpl var from file: ' . $tplVarFile);
            $tplVars = array_merge($tplVars, ConfigUtil::readFromFile($tplVarFile));
        }
        if ($userVars = $fs->getOpt('vars')) {
            $tplVars = array_merge($tplVars, $userVars);
        }

        $company = $tplVars['company'];
        $output->aList([
            // 'hasOld'     => $hasOld,
            'dryRun'     => $dryRun,
            'tplDir'     => $tplDir,
            'projectDir' => $workDir,
            'mainDir'    => "{projectDir}/src/main/java/com/$company/$namePath",
            'testDir'    => "{projectDir}/src/test/java/com/$company/$namePath",
        ]);

        $output->aList($tplVars, 'Context Vars');
        if ($output->unConfirm('Ensure init project')) {
            $output->colored('Quit, Bye!');
            return 0;
        }

        $ftb = FileTreeBuilder::new()
            ->setTplDir($tplDir)
            ->setTplVars($tplVars)
            ->setWorkdir($workDir)
            ->setDryRun($dryRun)
            ->setShowMsg(true);

        // set template render function
        $tplEng = EasyTemplate::newTexted(['tplDir' => $tplDir, 'tmpDir' => Kite::getTmpPath('ijp_tpl_caches')]);
        $ftb->setRenderFn(fn(string $tplFile, array $vars): string => $tplEng->renderFile($tplFile, $vars));

        // load bootstrap file and create file tree.
        $this->loadBootstrapFile($fs, $cfg, $ftb);

        $this->afterInitFiles($fs, $output);
        $output->println('INIT COMPLETED!');
        return 0;
    }

    /**
     * Loads the bootstrap file specified by the 'boot-file' option from the FlagsParser object.
     *
     * @param FlagsParser     $fs The FlagsParser object containing the command line flags.
     * @param ConfigBox       $cfg
     * @param FileTreeBuilder $ftb The FileTreeBuilder object used to build the file tree.
     *
     * @return void
     */
    private function loadBootstrapFile(FlagsParser $fs, ConfigBox $cfg, FileTreeBuilder $ftb): void
    {
        $bootFile = $fs->getOpt('boot-file', $cfg->getString('boot_file'));
        FS::assertIsFile($bootFile);
        Cli::info("Load bootstrap file: $bootFile");

        require $bootFile; // load bootstrap file with $ftb
    }

    private function afterInitFiles(FlagsParser $fs, Output $output): void
    {
        if ($fs->getOpt('add-git')) {
            $cr = CmdRunner::new();
            $cr->git('add', '.')
                ->git('commit', '-m', ':tada: new: init project');

            $lastBr = 'fea_init';
            if ($brs = $fs->getOptStrAsArray('branches')) {
                $output->colored("Begin init branches: " . implode(',', $brs));

                foreach ($brs as $br) {
                    $cr->git('checkout', '-b', $br);
                    $lastBr = $br;
                }
            } else {
                $cr->git('checkout', '-b', $lastBr);
            }

            $cr->git('push', '-u', 'origin', $lastBr)->flushRun();
        }

    }

    /**
     * @param string $project
     *
     * @return string
     */
    private function getTypeFromName(string $project): string
    {
        if (str_contains($project, 'service')) {
            $type = 'service';
        } elseif (str_contains($project, 'gateway')) {
            $type = 'gateway';
        } elseif (str_contains($project, 'bff')) {
            $type = 'bff';
        } else {
            $type = 'common';
        }
        return $type;
    }
}
