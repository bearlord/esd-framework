<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\Aop;

use ESD\Core\Plugins\Config\BaseConfig;
use ESD\Core\Plugins\Config\ConfigException;

/**
 * Class AopConfig
 * @package ESD\Plugins\Aop
 */
class AopConfig extends BaseConfig
{
    const KEY = "aop";

    /**
     * Cache directory
     * @var string
     */
    protected $cacheDir;

    /**
     * Include paths restricts the directories where aspects should be applied
     * @var string[]
     */
    protected $includePaths = [];

    /**
     * Exclude paths restricts the directories where aspects should be applied
     * @var string[]
     */
    protected $excludePaths = [];

    /**
     * Whether to cache files, default memory cache
     * @var bool
     */
    protected $fileCache = false;

    /**
     * @var OrderAspect[]
     */
    private $aspects = [];

    /**
     * AopConfig constructor.
     * @param mixed ...$includePaths
     */
    public function __construct(...$includePaths)
    {
        parent::__construct(self::KEY);
        foreach ($includePaths as $includePath) {
            $this->addIncludePath($includePath);
        }
    }

    /**
     * @return string
     */
    public function getCacheDir()
    {
        return $this->cacheDir;
    }

    /**
     * @param string $cacheDir
     */
    public function setCacheDir(string $cacheDir): void
    {
        $this->cacheDir = $cacheDir;
    }

    /**
     * @return string[]
     */
    public function getIncludePaths()
    {
        return $this->includePaths;
    }

    /**
     * @param string[] $includePaths
     */
    public function setIncludePaths(array $includePaths): void
    {
        $this->includePaths = $includePaths;
    }

    /**
     * @param string $includePath
     */
    public function addIncludePath(string $includePath)
    {
        $includePath = realpath($includePath);
        if ($includePath === false) {
            return;
        }
        $key = str_replace(realpath(ROOT_DIR), "", $includePath);
        $key = str_replace("/", ".", $key);
        $this->includePaths[$key] = $includePath;
    }

    /**
     * @param OrderAspect $param
     */
    public function addAspect(OrderAspect $param)
    {
        $this->aspects[] = $param;
    }

    /**
     * @return OrderAspect[]
     */
    public function getAspects(): array
    {
        return $this->aspects;
    }

    /**
     * Build config
     * @throws ConfigException
     */
    public function buildConfig()
    {
        if (empty($this->includePaths)) {
            throw new ConfigException("includePaths cannot be empty");
        }
    }

    /**
     * @return bool
     */
    public function isFileCache(): bool
    {
        return $this->fileCache;
    }

    /**
     * @param bool $fileCache
     */
    public function setFileCache(bool $fileCache): void
    {
        $this->fileCache = $fileCache;
    }

    /**
     * @return string[]
     */
    public function getExcludePaths(): array
    {
        return $this->excludePaths;
    }

    /**
     * @param string[] $excludePaths
     */
    public function setExcludePaths(array $excludePaths): void
    {
        $this->excludePaths = $excludePaths;
    }

    /**
     * @param string $excludePath
     */
    public function addExcludePath(string $excludePath)
    {
        $excludePath = realpath($excludePath);
        if ($excludePath === false) {
            return;
        }
        $key = str_replace(realpath(ROOT_DIR), "", $excludePath);
        $key = str_replace("/", ".", $key);
        $this->excludePaths[$key] = $excludePath;
    }
}