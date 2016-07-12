<?php
/**
 * Part of phancap
 *
 * PHP version 5
 *
 * @category  Tools
 * @package   Phancap
 * @author    Christian Weiske <cweiske@cweiske.de>
 * @copyright 2014 Christian Weiske
 * @license   http://www.gnu.org/licenses/agpl.html GNU AGPL v3
 * @link      http://cweiske.de/phancap.htm
 */
namespace phancap;

/**
 * Embed meta data into the given file
 *
 * @category  Tools
 * @package   Phancap
 * @author    Christian Weiske <cweiske@cweiske.de>
 * @copyright 2016 Christian Weiske
 * @license   http://www.gnu.org/licenses/agpl.html GNU AGPL v3
 * @version   Release: @package_version@
 * @link      http://cweiske.de/phancap.htm
 */
class MetaData
{
    /**
     * Add meta data to the given image
     *
     * @param Image   $img     Image object to render (contains name)
     * @param Options $options Image rendering options
     *
     * @return void
     * @throws \Exception When something goes wrong
     */
    public function embed(Image $img, Options $options)
    {
        $modes = array(
            'screen' => 'browser window (screen)',
            'page'   => 'full page height',
        );

        $title = "Screenshot of " . $options->values['url'];
        $desc = "Capture options:"
            . sprintf(
                ' browser window: %dx%d',
                $options->values['bwidth'],
                $options->values['bheight']
            )
            . sprintf(
                ', screenshot size: %dx%d',
                $options->values['swidth'],
                $options->values['sheight']
            )
            . ', format: ' . $options->values['sformat']
            . ', mode: ' . $modes[$options->values['smode']];

        Executor::run(
            'exiftool'
            . ' -XMP:title=' . escapeshellarg($title)
            . ' -XMP:source=' . escapeshellarg($img->getUrl())
            . ' -XMP:subject=' . escapeshellarg($options->values['url'])
            . ' -XMP:creator=' . escapeshellarg('phancap')
            . ' -XMP:description=' . escapeshellarg($desc)
            . ' ' . escapeshellarg($img->getPath()),
            'exiftool'
        );
    }
}
?>
