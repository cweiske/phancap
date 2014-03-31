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

    /**
     * Credentials for access
     * username => secret key (used for signature)
     * @var array
     */
    public $access = false;

    /**
     * How long requests with an old timestamp may be used.
     * 2 days default.
     *
     * @var integer
     */
    public $timestampLifetime = 172800;


    public function __construct()
    {
        $this->cacheDir    = getcwd() . '/imgcache/';
        $this->cacheDirUrl = $this->getCurrentUrlDir() . '/imgcache/';
    }

    public function load()
    {
        $cfgFile = __DIR__ . '/../../data/phancap.config.php';
        if (file_exists($cfgFile)) {
            $this->loadFile($cfgFile);
        }

        $this->setupCheck();
    }

    protected function loadFile($filename)
    {
        include $filename;
        $vars = get_defined_vars();
        foreach ($vars as $k => $value) {
            $this->$k = $value;
        }
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
