<?php
/**
 * Phar stub file for bdrem. Handles startup of the .phar file.
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
if (!in_array('phar', stream_get_wrappers()) || !class_exists('Phar', false)) {
    echo "Phar extension not avaiable\n";
    exit(255);
}

$web = 'www/index.php';

/**
 * Rewrite the HTTP request path to an internal file.
 * Maps "" and "/" to "www/index.php".
 *
 * @param string $path Path from the browser, relative to the .phar
 *
 * @return string Internal path.
 */
function rewritePath($path)
{
    if ($path == '' || $path == '/') {
        return 'www/index.php';
    } else if ($path == '/get' || $path == '/get.php') {
        return 'www/get.php';
    } else if ($path == '/setup' || $path == '/setup.php') {
        return 'www/setup.php';
    }
    return $path;
}

//Phar::interceptFileFuncs();
set_include_path(
    'phar://' . __FILE__
    . PATH_SEPARATOR . 'phar://' . __FILE__ . '/lib/'
);
Phar::webPhar(null, $web, null, array(), 'rewritePath');

//work around https://bugs.php.net/bug.php?id=52322
//require 'phar://' . __FILE__ . '/' . $web;
echo "cli\n";
__HALT_COMPILER();
?>
