<?php declare(strict_types=1);

namespace Inhere\PTool\ManDoc;

use Inhere\PTool\Exception\TopicNotFoundException;
use function glob;
use function implode;
use function is_dir;
use function is_string;
use function rtrim;

/**
 * Class ManDocument
 *
 * @package Inhere\PTool\ManDoc
 */
class Document
{
    public const EXT = '.md';

    /**
     * @var array
     */
    private $paths;

    /**
     * @var string
     */
    private $lang;

    /**
     * @var array
     */
    private $realPaths = [];

    /**
     * @var array
     */
    private $errorPaths = [];

    /**
     * @var DocTopic[]
     */
    private $topics = [];

    /**
     * @var array [name => isDir, ]
     */
    private $topicNames = [];

    /**
     * Class constructor.
     *
     * @param array  $paths
     * @param string $lang
     */
    public function __construct(array $paths, string $lang = 'en')
    {
        $this->paths = $paths;
        $this->lang  = $lang;
    }

    /**
     * prepare
     */
    public function prepare(): void
    {
        $this->loadRealPaths();

        $this->loadTopTopics();
    }

    /**
     * load real paths
     *
     * @return array
     */
    private function loadRealPaths(): array
    {
        if ($this->realPaths) {
            return $this->realPaths;
        }

        foreach ($this->paths as $key => $path) {
            // with language
            if (is_dir($realPath = $path . '/' . $this->lang)) {
                $this->addRealPath($key, $realPath);
            } elseif (is_dir($path)) { // no language
                $this->addRealPath($key, $path);
            } else {
                $this->addErrorPath($key, $path);
            }
        }

        return $this->realPaths;
    }

    /**
     * @param int|string $key
     * @param string     $path
     */
    private function addRealPath($key, string $path): void
    {
        $path = rtrim($path, '/');

        if (is_string($key)) {
            $this->realPaths[$key] = $path;
        } else {
            $this->realPaths[] = $path;
        }
    }

    /**
     * @param int|string $key
     * @param string     $path
     */
    private function addErrorPath($key, string $path): void
    {
        if (is_string($key)) {
            $this->errorPaths[$key] = $path;
        } else {
            $this->errorPaths[] = $path;
        }
    }

    /**
     * @return void
     */
    private function loadTopTopics(): void
    {
        // $flags  = GLOB_ONLYDIR | GLOB_MARK;
        $flags = GLOB_MARK;

        foreach ($this->realPaths as $path) {
            foreach (glob($path . '/*', $flags) as $subPath) {
                $isDir = is_dir($subPath);
                // parse for get name
                [$tpName, $fsName] = DocTopic::parseSubPath($subPath, $isDir);

                // invalid file or dir
                if (!$tpName) {
                    continue;
                }

                // create topic object
                $topic = DocTopic::create($tpName, $subPath, $fsName, $isDir);

                // save name
                $this->topicNames[$tpName] = $isDir;
                // add topics
                $this->topics[$tpName] = $topic;
            }
        }
    }

    /**
     * @param string $top
     * @param array  $subs
     *
     * @return DocTopic|null
     */
    public function findTopic(string $top, array $subs = []): ?DocTopic
    {
        if (!isset($this->topics[$top])) {
            return null;
        }

        $topic = $this->topics[$top];
        if (!$subs) {
            return $topic;
        }

        // find sub topic
        foreach ($subs as $sub) {
            if ($child = $topic->load()->getChild($sub)) {
                $topic = $child;
            } else {
                return null;
            }
        }

        return $topic;
    }

    /**
     * @param string $top
     * @param array  $subs
     *
     * @return DocTopic
     */
    public function mustFindTopic(string $top, array $subs = []): DocTopic
    {
        if (!isset($this->topics[$top])) {
            throw new TopicNotFoundException($top);
        }

        if (!$subs) {
            return $this->topics[$top];
        }

        $topic = $this->topics[$top];
        foreach ($subs as $sub) {
            if ($child = $topic->load()->getChild($sub)) {
                $topic = $child;
            } else {
                throw new TopicNotFoundException($sub, $topic->getName());
            }
        }

        return $topic;
    }

    /**
     * @return array
     */
    public function findTopics(): array
    {
        return [];
    }

    /**
     * @param string $top
     * @param array  $subs
     *
     * @return array
     */
    public function iterTopics(string $top = '', array $subs = []): array
    {
        return [];
    }

    /**
     * @param string $top
     * @param array  $subs
     *
     * @return array
     */
    public function getTopicsInfo(string $top = '', array $subs = []): array
    {
        $topics = [];
        $hidden = ['level', 'isDir', 'parent', 'childs'];

        if (!$top) {
            foreach ($this->topics as $name => $topic) {
                $topics[$name] = $topic->load()->toArray($hidden);
            }

            return $topics;
        }

        $topic = $this->findTopic($top, $subs);
        if (!$topic) {
            return [];
        }

        foreach ($topic->load()->getChilds() as $name => $topic) {
            $topics[$name] = $topic->toArray($hidden);
        }

        return $topics;
    }

    /**
     * @param string $top
     * @param array  $subs
     *
     * @return string
     */
    public static function names2string(string $top, array $subs): string
    {
        if (!$top) {
            return 'TOP';
        }

        if (!$subs) {
            return $top;
        }

        return $top . '>' . implode('>', $subs);
    }


    /**
     * @return array
     */
    public function getPaths(): array
    {
        return $this->paths;
    }

    /**
     * @param array $paths
     */
    public function setPaths(array $paths): void
    {
        $this->paths = $paths;
    }

    /**
     * @return string
     */
    public function getLang(): string
    {
        return $this->lang;
    }

    /**
     * @param string $lang
     */
    public function setLang(string $lang): void
    {
        if ($lang) {
            $this->lang = $lang;
        }
    }

    /**
     * @return array
     */
    public function getRealPaths(): array
    {
        return $this->realPaths;
    }

    /**
     * @return array
     */
    public function getErrorPaths(): array
    {
        return $this->errorPaths;
    }

    /**
     * @return array
     */
    public function getTopics(): array
    {
        return $this->topics;
    }

    /**
     * @return array
     */
    public function getTopicNames(): array
    {
        return $this->topicNames;
    }
}
