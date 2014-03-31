<?php
namespace phancap;

class Config
{
    /**
     * Full file system path to cache directory
     * @var string
     */
    public $cacheDir;

    /**
     * Full URL to cache directory
     * @var string
     */
    public $cacheDirUrl;


    public function __construct()
    {
        $this->cacheDir    = getcwd() . '/imgcache/';
        $this->cacheDirUrl = $this->getCurrentUrlDir() . '/imgcache/';
    }

    public function setupCheck()
    {
        if (!is_dir($this->cacheDir)) {
            throw new \Exception('Cache directory does not exist: ' . $this->cacheDir);
        }
        if (!is_writable($this->cacheDir)) {
            throw new \Exception('Cache directory is not writable: ' . $this->cacheDir);
        }
    }

    protected function getCurrentUrl()
    {
        if (!isset($_SERVER['REQUEST_SCHEME'])) {
            $_SERVER['REQUEST_SCHEME'] = 'http';
        }
        return $_SERVER['REQUEST_SCHEME'] . '://'
            . $_SERVER['HTTP_HOST']
            . preg_replace('/#.*$/', '', $_SERVER['REQUEST_URI']);
    }

    protected function getCurrentUrlDir()
    {
        $url = $this->getCurrentUrl();
        $url = preg_replace('/\?.*$/', '', $url);
        if (substr($url, -1) == '/') {
            return $url;
        }

        return substr($url, 0, -strlen(basename($url)) - 1);
    }
}
?>
