<?php
namespace phancap;

class Adapter_Cutycapt
{
    protected $lockHdl;
    protected $lockFile = null;

    /**
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

        error_reporting($old);
        if (count($arErrors)) {
            return $arErrors;
        }

        return true;
    }

    public function render(Image $img, Options $options)
    {
        $format = $options->values['sformat'];
        if ($format == 'jpg') {
            $format = 'jpeg';
        }

        $serverNumber = $this->getServerNumber($options);
        $tmpPath = $img->getPath() . '-tmp';
        $cmd = 'cutycapt'
            . ' --url=' . escapeshellarg($options->values['url'])
            . ' --out-format=' . escapeshellarg($format)
            . ' --out=' . escapeshellarg($tmpPath)
            . ' --max-wait=10000'
            . ' --min-width=' . $options->values['bwidth'];
        if ($options->values['bheight'] !== null) {
            $cmd .= ' --min-height=' . $options->values['bheight'];
        }

        $xvfbcmd = 'xvfb-run'
            . ' -e /dev/stdout'
            . ' --server-args="-screen 0, 1024x768x24"'
            . ' --server-num=' . $serverNumber;
        Executor::run($xvfbcmd . ' ' . $cmd);

        $this->resize($tmpPath, $img, $options);
    }

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

    protected function resize($tmpPath, $img, $options)
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

    public function setConfig(Config $config)
    {
        $this->config = $config;
    }
}
?>
