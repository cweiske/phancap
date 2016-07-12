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
     * @param string $cmd  Full command including parameters and options
     * @param string $name Command name for exception
     *
     * @return void
     * @throws \Exception When the exit code is not 0
     */
    public static function run($cmd, $name)
    {
        exec($cmd . ' 2>&1', $arOutput, $exitcode);
        if ($exitcode != 0) {
            //FIXME: do something with $arOutput
            //echo implode("\n", $arOutput) . "\n";
            throw new \Exception('Error running ' . $name, $exitcode);
        }
    }

    /**
     * Let the command run for some time. Kill it if it did not exit itself.
     *
     * We use the GNU coreutils "timeout" utility instead of the pcntl
     * extension since pcntl is disabled on mod_php.
     *
     * @param string $cmd     Full command including parameters and options
     * @param int    $seconds Number of seconds after which the cmd is killed
     * @param string $name    Command name for exception
     *
     * @return void
     * @throws \Exception When the exit code is not 0
     */
    public static function runForSomeTime($cmd, $seconds, $name)
    {
        return static::run(
            'timeout --signal=9 ' . $seconds . 's ' . $cmd,
            $name
        );
    }
}
?>
