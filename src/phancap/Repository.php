<?php
/**
 * Part of phancap
 *
 * PHP version 5
 *
 * @category  Tools
 * @package   Repository
 * @author    Christian Weiske <cweiske@cweiske.de>
 * @copyright 2014 Christian Weiske
 * @license   http://www.gnu.org/licenses/agpl.html GNU AGPL v3
 * @link      http://cweiske.de/phancap.htm
 */
namespace phancap;

/**
 * Repository of existing screenshots
 *
 * @category  Tools
 * @package   Repository
 * @author    Christian Weiske <cweiske@cweiske.de>
 * @copyright 2014 Christian Weiske
 * @license   http://www.gnu.org/licenses/agpl.html GNU AGPL v3
 * @version   Release: @package_version@
 * @link      http://cweiske.de/phancap.htm
 */
class Repository
{
    /**
     * Return image object for the given rendering options.
     * Loads it from cache if possible.
     * If cache is expired or image does not yet exist,
     * it will be rendered.
     *
     * @param Options $options Image rendering options
     *
     * @return Image Image object
     */
    public function getImage(Options $options)
    {
        $name = $this->getFilename($options);
        $img = new Image($name);
        $img->setConfig($this->config);
        if (!$this->isAvailable($img, $options)) {
            $this->render($img, $options);
        }
        return $img;
    }

    /**
     * Get the cache image filename for the given rendering options
     *
     * @param Options $options Image rendering options
     *
     * @return string relative file name for the image
     */
    public function getFilename(Options $options)
    {
        $optValues = $options->values;
        unset($optValues['smaxage']);
        unset($optValues['atimestamp']);
        unset($optValues['asignature']);
        unset($optValues['atoken']);

        return parse_url($optValues['url'], PHP_URL_HOST)
            . '-' . md5(\serialize($optValues))
            . '.' . $optValues['sformat'];
    }

    /**
     * Check if the image is available locally.
     *
     * @param Image   $img     Image object to render (contains name)
     * @param Options $options Image rendering options
     *
     * @return boolean True if we have it and it's within the cache lifetime,
     *                 false if the cache expired or the screenshot does not
     *                 exist.
     */
    public function isAvailable(Image $img, Options $options)
    {
        $path = $img->getPath();
        if (!file_exists($path)) {
            return false;
        }

        if (filemtime($path) < time() - $options->values['smaxage']) {
            return false;
        }

        return true;
    }

    /**
     * Render a website screenshot
     *
     * @param Image   $img     Image object to render (contains name)
     * @param Options $options Image rendering options
     *
     * @return void
     * @throws \Exception When something goes wrong
     */
    protected function render(Image $img, Options $options)
    {
        $adapter = new Adapter_Cutycapt();
        $adapter->setConfig($this->config);
        try {
            $adapter->render($img, $options);
            $adapter->cleanup();
        } catch (\Exception $e) {
            $adapter->cleanup();
            throw $e;
        }

        $meta = new MetaData();
        $meta->embed($img, $options);
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
