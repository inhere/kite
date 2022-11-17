<?php declare(strict_types=1);

namespace Inhere\Kite\Lib\Jenkins;

use PhpPkg\JenkinsClient\Jenkins;
use RuntimeException;
use function array_merge;
use function strtr;

/**
 * class JenkinsFactory
 *
 * # API for jenkins:
 *
 * ## Jobs info
 *
 * - Jobs info:
 *      - /api/json?tree=jobs[*]  All info
 *      - /api/json?tree=jobs[name] limit return fields
 *      - commonly fields: description,name,fullName,displayName,url,buildable,inQueue,concurrentBuild
 * - show all info for a job: http://my-jenkins.dev/job/JOB_NAME/api/json?depth=0
 *
 * ## job property
 *
 * - job property parameters: /job/JOB_NAME/api/json?tree=property[parameterDefinitions[*]]
 * - limit returns fields: /job/JOB_NAME/api/json?tree=property[parameterDefinitions[description,name,type,choices]]
 *
 * ## job builds info
 *
 * - latest build and full info: /job/JOB_NAME/api/json?tree=lastBuild[*]
 * - latest N builds and full info: /job/JOB_NAME/api/json?tree=builds[*]{0,10}&depth=1
 * - latest N builds and limit fields: /job/JOB_NAME/api/json?tree=builds[number,queueId,displayName,building,duration,timestamp]{0,6}
 * - latest N builds and only field 'number': /job/JOB_NAME/api/json?tree=builds[number]{0,10}
 * - build page URL: /job/JOB_NAME/1746/
 * - console page URL: /job/JOB_NAME/1746/console
 *
 * @author inhere
 */
class JenkinsFactory extends JenkinsConfig
{
    private ?JenkinsConfig $defaultConfig = null;

    /**
     * View folder path.
     *
     * eg:
     * http://my-jenkins.dev/view/my-job-group/job/{job name}/buildWithParameters
     *
     * - folderPath: 'view/my-job-group'
     *
     * @var string
     */
    public string $folderPath = '';

    /**
     * @var string default job name
     */
    public string $jobName = '';

    /**
     * @var string current env name.
     */
    public string $envName = '';

    /**
     * can set config for diff env, multi jenkins.
     *
     * @var array = ['key' => ['username' => '', 'apiToken' => '', 'hostUrl' => '']]
     */
    public array $envInfo = [];

    /**
     * @param string $jobName
     *
     * @return $this
     */
    public function withJobName(string $jobName): self
    {
        $this->jobName = $jobName;
        return $this;
    }

    /**
     * @param string $env
     *
     * @return Jenkins
     */
    public function getJenkins(string $env = ''): Jenkins
    {
        return $this->create($env);
    }

    /**
     * @param string $env
     *
     * @return Jenkins
     */
    public function create(string $env = ''): Jenkins
    {
        $jc = $this->getEnvConfig($env);
        $jk = new Jenkins($jc->hostUrl);

        $jk->setUsername($jc->username);
        $jk->setPassword($jc->password);
        $jk->setApiToken($jc->apiToken);

        return $jk;
    }

    /**
     * @param string $pathTpl
     *
     * @return string
     */
    public function buildUrl(string $pathTpl): string
    {
        return $this->hostUrl . strtr($pathTpl, [
            '{folderPath}' => $this->folderPath,
            '{name}'       => $this->jobName,
        ]);
    }

    /**
     * @param string $envName
     *
     * @return $this
     */
    public function useEnv(string $envName): self
    {
        return $this->setEnvName($envName);
    }

    /**
     * @param string $envName
     *
     * @return $this
     */
    public function setEnvName(string $envName): self
    {
        if ($envName) {
            $this->envName = $envName;
        }
        return $this;
    }

    /**
     * @param string $envName
     *
     * @return JenkinsConfig
     */
    public function getEnvConfig(string $envName = ''): JenkinsConfig
    {
        $defConf = $this->getDefaultConfig();
        $envName = $envName ?: $this->envName;

        if ($envName) {
            if (isset($this->envInfo[$envName])) {
                return JenkinsConfig::new(array_merge($defConf->toArray(), $this->envInfo[$envName]));
            }

            throw new RuntimeException("get unknown env config: $envName");
        }

        return $defConf;
    }

    /**
     * @return JenkinsConfig
     */
    public function getDefaultConfig(): JenkinsConfig
    {
        if (!$this->defaultConfig) {
            $this->defaultConfig = JenkinsConfig::new([
                'hostUrl'  => $this->hostUrl,
                'username' => $this->username,
                'apiToken' => $this->apiToken,
            ]);
        }

        return $this->defaultConfig;
    }


}
