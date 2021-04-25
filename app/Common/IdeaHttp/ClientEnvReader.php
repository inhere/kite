<?php declare(strict_types=1);

namespace Inhere\Kite\Common\IdeaHttp;

use RuntimeException;
use Toolkit\Stdlib\Json;
use function array_keys;

/**
 * Class ClientEnvReader
 *
 * @package Inhere\Kite\Common\IdeaHttp
 */
class ClientEnvReader
{
    /**
     * @var string
     */
    private $envFile;

    /**
     * @var array
     */
    private $envs = [];

    /**
     * @param string $envFile
     *
     * @return static
     */
    public static function new(string $envFile): self
    {
        return new self($envFile);
    }

    /**
     * Class constructor.
     *
     * @param string $envFile
     */
    public function __construct(string $envFile)
    {
        $this->envFile = $envFile;
    }

    /**
     * @param bool $mustLoad
     *
     * @return bool
     */
    public function load(bool $mustLoad = true): bool
    {
        $envFile = $this->envFile;

        if (!file_exists($envFile)) {
            if ($mustLoad) {
                throw new RuntimeException('the http-client env file not exists. file: ' . $envFile);
            }

            return false;
        }

        $jsonString = file_get_contents($envFile);

        // load data
        $this->envs = Json::decode($jsonString, true);
        return true;
    }

    /**
     * @param string $name
     */
    public function useEnv(string $name): void
    {
        $this->curEnv = $name;
    }

    /**
     * @param string $name
     *
     * @return ClientEnvData|null
     */
    public function getEnvData(string $name): ?ClientEnvData
    {
        if (isset($this->envs[$name])) {
            return new ClientEnvData($this->envs[$name]);
        }

        return null;
    }

    /**
     * @param string $name
     *
     * @return array
     */
    public function getEnvArray(string $name): array
    {
        return $this->envs[$name] ?? [];
    }

    /**
     * @return array
     */
    public function getEnvNames(): array
    {
        return array_keys($this->envs);
    }

    /**
     * @return string
     */
    public function getEnvFile(): string
    {
        return $this->envFile;
    }

    /**
     * @param string $envFile
     */
    public function setEnvFile(string $envFile): void
    {
        $this->envFile = $envFile;
    }

    /**
     * @return array
     */
    public function getEnvs(): array
    {
        return $this->envs;
    }
}
