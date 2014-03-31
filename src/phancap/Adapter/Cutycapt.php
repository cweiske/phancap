<?php
namespace phancap;

class Adapter_Cutycapt
{
    public function isAvailable()
    {
        //FIXME: setup check for xvfbrun, cutycapt, convert
    }

    public function render(Image $img, Options $options)
    {
        $tmpPath = $img->getPath() . '-tmp';
        $cmd = 'cutycapt'
            . ' --url=' . escapeshellarg($options->values['url'])
            . ' --out-format=' . escapeshellarg($options->values['sformat'])
            . ' --out=' . escapeshellarg($tmpPath)
            . ' --max-wait=10000'
            . ' --min-width=' . $options->values['bwidth'];
        if ($options->values['bheight'] !== null) {
            $cmd .= ' --min-height=' . $options->values['bheight'];
        }

        $xvfbcmd = 'xvfb-run'
            . ' -e /dev/stdout'
            . ' --server-args="-screen 0, 1024x768x24"';
        Executor::run($xvfbcmd . ' ' . $cmd);

        $this->resize($tmpPath, $img, $options);
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
}
?>
