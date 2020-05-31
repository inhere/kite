<?php declare(strict_types=1);

namespace Inhere\Kite\ManDoc;

use function count;
use function file_get_contents;

/**
 * Class DocTopic
 *
 * @package Inhere\Kite\Common
 */
class DocTopic
{
    public const EXT = '.md';
    // The default doc filename: default.md
    public const DEF = 'default';

    /**
     * @var self|null
     */
    private $parent;

    /**
     * The topic childs. key is name
     *
     * @var self[]
     */
    private $childs = [];

    /**
     * @var array [name => isDir, ]
     */
    private $childNames = [];

    /**
     * The topic name. eg: git, tmux
     *
     * @var string
     */
    private $name;

    /**
     * @var int
     */
    private $level = 1;

    /**
     * @var string
     */
    private $path;

    /**
     * The file/dir name. eg: git, tmux.md
     *
     * @var string
     */
    private $fsName;

    /**
     * @var bool
     */
    private $isDir;

    /**
     * @var array
     */
    private $info = [];

    /**
     * @var bool
     */
    private $loaded = false;

    /**
     * @param string $name
     * @param string $path
     * @param string $fsName
     * @param bool   $isDir
     *
     * @return static
     */
    public static function create(string $name, string $path, string $fsName, bool $isDir = false): self
    {
        return new self($name, $path, $fsName, $isDir);
    }

    /**
     * Class constructor.
     *
     * @param string $name
     * @param string $path
     * @param string $fsName
     * @param bool   $isDir
     */
    public function __construct(string $name, string $path, string $fsName, bool $isDir = false)
    {
        $this->name  = $name;
        $this->path  = $path;
        $this->isDir = $isDir;

        $this->fsName = $fsName;
    }

    /**
     * @return $this
     */
    public function load(): self
    {
        if (!$this->loaded) {
            $this->loadChildTopics();
        }

        return $this;
    }

    /**
     * @return void
     */
    private function loadChildTopics(): void
    {
        $this->loaded = true;

        // current is file doc topic
        if (!$this->isDir) {
            return;
        }

        // $flags  = GLOB_ONLYDIR | GLOB_MARK;
        $flags = GLOB_MARK;

        // yield from glob($pattern, $flags);
        foreach (glob($this->path . '*', $flags) as $subPath) {
            $isDir = is_dir($subPath);
            // parse for get name
            [$tpName, $fsName] = self::parseSubPath($subPath, $isDir);

            // invalid file or dir
            if (!$tpName) {
                continue;
            }

            // create topic object
            $topic = new self($tpName, $subPath, $fsName, $isDir);
            // save name
            $this->childNames[$tpName] = $isDir;

            // add topics
            $this->addChild($topic);
        }
    }

    /**
     * @param string $subPath
     * @param bool   $isDir
     *
     * @return array
     */
    public static function parseSubPath(string $subPath, bool $isDir): array
    {
        if ($isDir) {
            $tpName = $fsName = basename($subPath);
        } else {
            $tpName = '';
            $fsName = basename($subPath);
            if (substr($fsName, -3) === self::EXT) {
                $tpName = substr($fsName, 0, -3);
            }
        }

        return [$tpName, $fsName];
    }

    /**
     * @param array $subs
     *
     * @return $this|null
     */
    public function findTopicByPaths(array $subs): ?self
    {
        $topic = null;
        foreach ($subs as $sub) {
            if ($child = $this->load()->getChild($sub)) {
                $topic = $child;
            } else {
                $topic = null;
                break;
            }
        }

        return $topic;
    }

    /**
     * @return $this|null
     */
    public function getDocFile(): ?self
    {
        if ($this->isDir) {
            return $this->getDefault();
        }

        return $this;
    }

    /**
     * Get the default file document
     *
     * @return $this|null
     */
    public function getDefault(): ?self
    {
        return $this->load()->getChild(self::DEF);
    }

    /**
     * @return string
     */
    public function getFileContent(): string
    {
        if ($this->isDir) {
            return '';
        }

        return file_get_contents($this->path);
    }

    /**
     * @param array|null $hidden
     *
     * @return array
     */
    public function getChildsInfo(array $hidden = null): array
    {
        $topics = [];

        if (null === $hidden) {
            $hidden = ['level', 'isDir', 'parent', 'childs'];
        }

        foreach ($this->load()->getChilds() as $name => $topic) {
            $topics[$name] = $topic->toArray($hidden);
        }

        return $topics;
    }

    /**
     * @param bool  $parentAsName
     * @param array $hidden
     *
     * @return array
     */
    public function toArray(array $hidden = [], bool $parentAsName = true): array
    {
        if ($parentAsName) {
            $parent = $this->parent ? $this->parent->getName() : '-';
        } else {
            $parent = $this->parent;
        }

        $map = [
            'topic'  => $this->name,
            'level'  => $this->level,
            'fsName' => $this->fsName,
            'isDir'  => $this->isDir,
            'path'   => $this->path,
            'parent' => $parent,
            'childs' => $this->hasChilds() ? '[CHILDS...]' : '-',
        ];

        if ($hidden) {
            foreach ($hidden as $key) {
                if (isset($map[$key])) {
                    unset($map[$key]);
                }
            }
        }

        return $map;
    }

    /**
     * @param self $topic
     */
    public function addChild(self $topic): void
    {
        $topic->setParent($this);
        $topic->setLevel($this->level + 1);

        $this->childs[$topic->getName()] = $topic;
    }

    /**
     * @param string $name
     *
     * @return self|null
     */
    public function getChild(string $name): ?self
    {
        return $this->childs[$name] ?? null;
    }

    /**
     * @return self
     */
    public function getTopParent(): self
    {
        if ($this->parent === null) {
            return $this;
        }

        return $this->parent->getTopParent();
    }

    /**
     * @return bool
     */
    public function isTopTopic(): bool
    {
        return $this->parent === null;
    }

    /**
     * @return bool
     */
    public function isEndTopic(): bool
    {
        return false === $this->hasChilds();
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function hasChild(string $name): bool
    {
        return isset($this->childs[$name]);
    }

    /**
     * @return bool
     */
    public function hasChilds(): bool
    {
        return count($this->childs) > 0;
    }

    /**
     * @return DocTopic
     */
    public function getParent(): ?DocTopic
    {
        return $this->parent;
    }

    /**
     * @param DocTopic $parent
     */
    public function setParent(DocTopic $parent): void
    {
        $this->parent = $parent;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return array
     */
    public function getInfo(): array
    {
        return $this->info;
    }

    /**
     * @param array $info
     */
    public function setInfo(array $info): void
    {
        $this->info = $info;
    }

    /**
     * @return int
     */
    public function getLevel(): int
    {
        return $this->level;
    }

    /**
     * @param int $level
     */
    public function setLevel(int $level): void
    {
        $this->level = $level;
    }

    /**
     * @return bool
     */
    public function isFile(): bool
    {
        return false === $this->isDir;
    }

    /**
     * @return bool
     */
    public function isDir(): bool
    {
        return $this->isDir;
    }

    /**
     * @param bool $isDir
     */
    public function setIsDir(bool $isDir): void
    {
        $this->isDir = $isDir;
    }

    /**
     * @return string
     */
    public function getFsName(): string
    {
        return $this->fsName;
    }

    /**
     * @param string $fsName
     */
    public function setFsName(string $fsName): void
    {
        $this->fsName = $fsName;
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @param string $path
     */
    public function setPath(string $path): void
    {
        $this->path = $path;
    }

    /**
     * @return DocTopic[]
     */
    public function getChilds(): array
    {
        return $this->childs;
    }

    /**
     * @param DocTopic[] $childs
     */
    public function setChilds(array $childs): void
    {
        $this->childs = $childs;
    }
}
