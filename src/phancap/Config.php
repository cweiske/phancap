<?php
/**
 * Part of phancap
 *
 * PHP version 5
 *
 * @category  Tools
 * @package   Config
 * @author    Christian Weiske <cweiske@cweiske.de>
 * @copyright 2014 Christian Weiske
 * @license   http://www.gnu.org/licenses/agpl.html GNU AGPL v3
 * @link      http://cweiske.de/phancap.htm
 */
namespace phancap;

/**
 * Phancap configuration
 *
 * @category  Tools
 * @package   Config
 * @author    Christian Weiske <cweiske@cweiske.de>
 * @copyright 2014 Christian Weiske
 * @license   http://www.gnu.org/licenses/agpl.html GNU AGPL v3
 * @version   Release: @package_version@
 * @link      http://cweiske.de/phancap.htm
 */
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
     * List of config file paths that were tried to load
     * @var array
     */
    public $cfgFiles = array();

    /**
     * If a configuration file could be found
     * @var boolean
     */
    public $cfgFileExists;

    /**
     * Credentials for access
     *
     * Array of
     *     username => secret key
     * entries (used for signature).
     *
     * Boolean true to allow access in every case,
     * false to completely disable it.
     *
     * @var array|boolean
     */
    public $access = true;

    /**
     * Disable the setup check tool
     * @var boolean
     */
    public $disableSetup = false;

    /**
     * Redirect the browser to the cache URL.
     * If disabled, the file is directly delivered.
     *
     * Helpful for debugging since it does not change the browser's URL.
     *
     * @var boolean
     */
    public $redirect = true;

    /**
     * How long requests with an old timestamp may be used.
     * 2 days default.
     *
     * @var integer
     */
    public $timestampMaxAge = 'P2D';

    /**
     * Cache time of downloaded screenshots.
     * When the file is as older than this, it gets re-created.
     * The user can override that using the "smaxage" parameter.
     *
     * Defaults to 1 week.
     *
     * @var integer Lifetime in seconds
     */
    public $screenshotMaxAge = 'P1W';

    /**
     * Minimum age of a screeshot.
     * A user cannot set the max age parameter below it.
     *
     * Defaults to 1 hour.
     *
     * @var integer Minimum lifetime in seconds
     */
    public $screenshotMinAge = 'PT1H';

    /**
     * Cutycapt adapter options
     */
    public $cutycapt = array();


    /**
     * Initialize default values and loads configuration file paths
     */
    public function __construct()
    {
        $this->cacheDir    = getcwd() . '/imgcache/';
        $this->cacheDirUrl = $this->getCurrentUrlDir() . '/imgcache/';

        $this->timestampMaxAge  = Options::validateAge($this->timestampMaxAge);
        $this->screenshotMaxAge = Options::validateAge($this->screenshotMaxAge);
        $this->screenshotMinAge = Options::validateAge($this->screenshotMinAge);

        $this->loadConfigFilePaths();
    }

    /**
     * Load the first configuration file that exists
     *
     * @return void
     * @uses   $cfgFileExists
     */
    public function load()
    {
        $this->cfgFileExists = false;
        foreach ($this->cfgFiles as $file) {
            if (file_exists($file)) {
                $this->cfgFileExists = true;
                $this->loadFile($file);
                break;
            }
        }

        $this->setupCheck();
    }

    /**
     * Load possible configuration file paths into $this->cfgFiles.
     *
     * @return void
     */
    protected function loadConfigFilePaths()
    {
        $pharFile = \Phar::running();
        if ($pharFile == '') {
            $this->cfgFiles[] = __DIR__ . '/../../data/phancap.config.php';
        } else {
            //remove phar:// from the path
            $this->cfgFiles[] = substr($pharFile, 7) . '.config.php';
        }

        //TODO: add ~/.config/phancap.php

        $this->cfgFiles[] = '/etc/phancap.php';
    }

    /**
     * Load values of a configuration file into this class
     *
     * @param string $filename Configuration file (.php)
     *
     * @return void
     */
    protected function loadFile($filename)
    {
        include $filename;
        $vars = get_defined_vars();
        foreach ($vars as $k => $value) {
            $this->$k = $value;
        }
    }

    /**
     * Check if the cache directory exists and is writable
     *
     * @return void
     * @throws \Exception When something is not ok
     */
    public function setupCheck()
    {
        if (!is_dir($this->cacheDir)) {
            throw new \Exception(
                'Cache directory does not exist: ' . $this->cacheDir
            );
        }
        if (!is_writable($this->cacheDir)) {
            throw new \Exception(
                'Cache directory is not writable: ' . $this->cacheDir
            );
        }
    }

    /**
     * Returns the current URL, without fragmet.
     *
     * @return string Full URL
     */
    protected function getCurrentUrl()
    {
        if (!isset($_SERVER['REQUEST_SCHEME'])) {
            $_SERVER['REQUEST_SCHEME'] = 'http';
        }
        return $_SERVER['REQUEST_SCHEME'] . '://'
            . $_SERVER['HTTP_HOST']
            . preg_replace('/#.*$/', '', $_SERVER['REQUEST_URI']);
    }

    /**
     * Returns the current URL without the file name in the path
     *
     * @return string Directory of URL without trailing slash,
     *                and without .phar file
     */
    protected function getCurrentUrlDir()
    {
        $url = $this->getCurrentUrl();
        $url = preg_replace('/\?.*$/', '', $url);
        if (substr($url, -1) != '/') {
            $url = substr($url, 0, -strlen(basename($url)) - 1);
        }
        if (\Phar::running()) {
            //remove .phar file name
            $url = substr($url, 0, -strlen(basename($url)) - 1);
        }

        return $url;
    }
}
?>
