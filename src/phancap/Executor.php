<?php
/**
 * Part of phancap
 *
 * PHP version 5
 *
 * @category  Tools
 * @package   Executor
 * @author    Christian Weiske <cweiske@cweiske.de>
 * @copyright 2014 Christian Weiske
 * @license   http://www.gnu.org/licenses/agpl.html GNU AGPL v3
 * @link      http://cweiske.de/phancap.htm
 */
namespace phancap;

/**
 * Run a shell command
 *
 * @category  Tools
 * @package   Executor
 * @author    Christian Weiske <cweiske@cweiske.de>
 * @copyright 2014 Christian Weiske
 * @license   http://www.gnu.org/licenses/agpl.html GNU AGPL v3
 * @version   Release: @package_version@
 * @link      http://cweiske.de/phancap.htm
 */
class Executor
{
    /**
     * Run a shell command and check exit code.
     *
     * @param string $cmd Full command including parameters and options
     *
     * @return void
     * @throws \Exception When the exit code is not 0
     */
    public static function run($cmd)
    {
        exec($cmd . ' 2>&1', $arOutput, $exitcode);
        if ($exitcode != 0) {
            //FIXME: do something with $arOutput
            echo implode("\n", $arOutput) . "\n";
            throw new \Exception('Error running cutycapt', $exitcode);
        }
    }

    /**
     * Let the command run for some time. Kill it if it did not exit itself.
     *
     * We use the GNU coreutils "timeout" utility instead of the pcntl
     * extension since pcntl is disabled on mod_php.
     *
     * @param string $cmd Full command including parameters and options
     *
     * @return void
     * @throws \Exception When the exit code is not 0
     */
    public static function runForSomeTime($cmd, $seconds)
    {
        return static::run(
            'timeout --signal=9 ' . $seconds . 's ' . $cmd
        );
    }
}
?>
