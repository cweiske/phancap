<?php
namespace phancap;

class Executor
{
    public static function run($cmd)
    {
        exec($cmd . ' 2>&1', $arOutput, $exitcode);
        if ($exitcode != 0) {
            //FIXME: do something with $arOutput
            echo implode("\n", $arOutput) . "\n";
            throw new \Exception('Error running cutycapt', $exitcode);
        }
    }
}

?>
