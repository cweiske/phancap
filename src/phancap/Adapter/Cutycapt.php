<?php
/**
 * Part of phancap
 *
 * PHP version 5
 *
 * @category  Tools
 * @package   Adapter_Cutycapt
 * @author    Christian Weiske <cweiske@cweiske.de>
 * @copyright 2014 Christian Weiske
 * @license   http://www.gnu.org/licenses/agpl.html GNU AGPL v3
 * @link      http://cweiske.de/phancap.htm
 */
namespace phancap;

/**
 * Screenshot rendering using the "cutycapt" command line tool.
 *
 * @category  Tools
 * @package   Adapter_Cutycapt
 * @author    Christian Weiske <cweiske@cweiske.de>
 * @copyright 2014 Christian Weiske
 * @license   http://www.gnu.org/licenses/agpl.html GNU AGPL v3
 * @version   Release: @package_version@
 * @link      http://cweiske.de/phancap.htm
 */
class Adapter_Cutycapt
{
    /**
     * Lock file handle
     * @var resourece
     */
    protected $lockHdl;

    /**
     * Lock file path
     * @var string
     */
    protected $lockFile = null;

    /**
     * Check if all dependencies are available.
     *
     * @return mixed TRUE if all is fine, array with error messages otherwise
     */
    public function isAvailable()
    {
        $old = error_reporting(error_reporting() & ~E_STRICT);
        $arErrors = array();
        if (\System::which('xvfb-run') === false) {
            $arErrors[] = '"xvfb-run" is not installed';
        }
        if (\System::which('cutycapt') === false) {
            $arErrors[] = '"cutycapt" is not installed';
        }
        if (\System::which('convert') === false) {
            $arErrors[] = '"convert" (imagemagick) is not installed';
        }
        if (\System::which('timeout') === false) {
            $arErrors[] = '"timeout" (GNU coreutils) is not installed';
        }

        error_reporting($old);
        if (count($arErrors)) {
            return $arErrors;
        }

        return true;
    }

    /**
     * Render a website screenshot
     *
     * @param Image   $img     Image configuration
     * @param Options $options Screenshot configuration
     *
     * @return void
     * @throws \Exception When something fails
     */
    public function render(Image $img, Options $options)
    {
        $format = $options->values['sformat'];
        if ($format == 'jpg') {
            $format = 'jpeg';
        }

        $maxWaitTime = 30;//seconds
        if (isset($this->config->cutycapt['maxWaitTime'])) {
            $maxWaitTime = (int) $this->config->cutycapt['maxWaitTime'];
        }

        $parameters = '';
        if (isset($this->config->cutycapt['parameters'])) {
            $parameters = $this->config->cutycapt['parameters'];
        }

        $serverNumber = $this->getServerNumber($options);
        $tmpPath = $img->getPath() . '-tmp';
        $cmd = 'cutycapt'
            . ' --url=' . escapeshellarg($options->values['url'])
            . ' --out-format=' . escapeshellarg($format)
            . ' --out=' . escapeshellarg($tmpPath)
            . ' --max-wait=' . (($maxWaitTime - 1) * 1000)
            . ' --min-width=' . $options->values['bwidth'];
        if ($options->values['bheight'] !== null) {
            $cmd .= ' --min-height=' . $options->values['bheight'];
        }
        if (strlen($parameters) > 0) {
            $cmd .= ' ' . $parameters;
        }

        $xvfbcmd = 'xvfb-run'
            . ' -e /dev/stdout'
            . ' --server-args="-screen 0, 1024x768x24"'
            . ' --server-num=' . $serverNumber;
        //cutycapt hangs sometimes - https://sourceforge.net/p/cutycapt/bugs/8/
        // we kill it if it does not exit itself
        Executor::runForSomeTime($xvfbcmd . ' ' . $cmd, $maxWaitTime);

        //cutycapt does not report timeouts via exit status
        if (!file_exists($tmpPath)) {
            throw new \Exception('Error running cutycapt (wait timeout)', 1);
        }

        $this->resize($tmpPath, $img, $options);
    }

    /**
     * Get a free X server number.
     *
     * Each xvfb-run process needs its own free server number.
     * Needed for multiple parallel requests.
     *
     * @return integer Server number
     */
    protected function getServerNumber()
    {
        //clean stale lock files
        $this->cleanup();

        $num = 100;
        $bFound = false;
        do {
            ++$num;
            $f = $this->config->cacheDir . 'tmp-curlycapt-server-' . $num . '.lock';
            $this->lockHdl = fopen($f, 'w');
            if (flock($this->lockHdl, LOCK_EX | LOCK_NB)) {
                $this->lockFile = $f;
                $bFound = true;
                break;
            } else {
                fclose($this->lockHdl);
            }
        } while ($num < 200);

        if (!$bFound) {
            throw new \Exception('Too many requests running');
        }

        $this->lockFile = $f;
        return $num;
    }

    /**
     * Unlock lock file and clean up old lock files
     *
     * @return void
     */
    public function cleanup()
    {
        if ($this->lockFile !== null && $this->lockHdl) {
            flock($this->lockHdl, LOCK_UN);
            unlink($this->lockFile);
        }

        $lockFiles = glob(
            $this->config->cacheDir . 'tmp-curlycapt-server-*.lock'
        );

        $now = time();
        foreach ($lockFiles as $file) {
            if ($now - filemtime($file) > 120) {
                //delete stale lock file; probably something crashed.
                unlink($file);
            }
        }
    }

    /**
     * Convert an image to the given size.
     *
     * Target file is the path of $img.
     *
     * @param string  $tmpPath Path of image to be scaled.
     * @param Image   $img     Image configuration
     * @param Options $options Screenshot configuration
     *
     * @return void
     */
    protected function resize($tmpPath, Image $img, Options $options)
    {
        if ($options->values['sformat'] == 'pdf') {
            //nothing to resize.
            rename($tmpPath, $img->getPath());
            return;
        }

        $crop = '';
        if ($options->values['smode'] == 'screen') {
            $crop = ' -crop ' . $options->values['swidth']
                . 'x' . $options->values['sheight']
                . '+0x0';
        }

        $convertcmd = 'convert'
            . ' ' . escapeshellarg($tmpPath)
            . ' -resize ' . $options->values['swidth']
            . $crop
            . ' ' . escapeshellarg($img->getPath());
        Executor::run($convertcmd);
        //var_dump($convertcmd);die();
        unlink($tmpPath);
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
