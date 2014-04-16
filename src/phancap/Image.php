<?php
/**
 * Part of phancap
 *
 * PHP version 5
 *
 * @category  Tools
 * @package   Image
 * @author    Christian Weiske <cweiske@cweiske.de>
 * @copyright 2014 Christian Weiske
 * @license   http://www.gnu.org/licenses/agpl.html GNU AGPL v3
 * @link      http://cweiske.de/phancap.htm
 */
namespace phancap;

/**
 * Image meta data and methods to get info about the file
 *
 * @category  Tools
 * @package   Image
 * @author    Christian Weiske <cweiske@cweiske.de>
 * @copyright 2014 Christian Weiske
 * @license   http://www.gnu.org/licenses/agpl.html GNU AGPL v3
 * @version   Release: @package_version@
 * @link      http://cweiske.de/phancap.htm
 */
class Image
{

    /**
     * Description for protected
     * @var object
     */
    protected $config;

    /**
     * Description for public
     * @var string
     */
    public $name;


    /**
     * Set the file name
     *
     * @param string $name Image file name
     */
    public function __construct($name)
    {
        $this->name = $name;
    }

    /**
     * Get the expiration date for the image, depending on
     * the maximum age the user requested.
     *
     * @param Options $options Image rendering options
     *
     * @return integer Unix timestamp
     */
    public function getExpiryDate(Options $options)
    {
        $mtime = filemtime($this->getPath());

        return $mtime + $options->values['smaxage'];
    }

    /**
     * Get the MIME type for the file
     *
     * @return string MIME type string ("image/png")
     */
    public function getMimeType()
    {
        $ext = substr($this->name, -4);
        if ($ext == '.jpg') {
            return 'image/jpeg';
        } else if ($ext == '.png') {
            return 'image/png';
        } else if ($ext == '.png') {
            return 'application/pdf';
        }
        return 'application/octet-stream';
    }

    /**
     * Get the full path to the cached image on disk
     *
     * @return string Full path to image
     */
    public function getPath()
    {
        return $this->config->cacheDir . $this->name;
    }

    /**
     * Get the public URL for the image
     *
     * @return string Public image URL
     */
    public function getUrl()
    {
        return $this->config->cacheDirUrl . $this->name;
    }

    /**
     * Set phancap configuration
     *
     * @param Config $config Phancap configuration
     *
     * @return void
     */
    public function setConfig(Config $config)
    {
        $this->config = $config;
    }
}
?>