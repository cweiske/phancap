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
    if ($path == '') {
        //we need a / to get the relative links on index.php work
        if (!isset($_SERVER['REQUEST_SCHEME'])) {
            $_SERVER['REQUEST_SCHEME'] = 'http';
        }
        $url = $_SERVER['REQUEST_SCHEME'] . '://'
            . $_SERVER['HTTP_HOST']
            . preg_replace('/[?#].*$/', '', $_SERVER['REQUEST_URI'])
            . '/';
        header('Location: ' . $url);
        exit(0);
    } else if ($path == '/') {
        return 'www/index.php';
    }

    if (substr($path, -4) == '.css') {
        header('Expires: ' . date('r', time() + 86400 * 7));
    }
    return 'www' . $path;
}

set_include_path(
    'phar://' . __FILE__
    . PATH_SEPARATOR . 'phar://' . __FILE__ . '/lib/'
);
Phar::webPhar(null, $web, null, array(), 'rewritePath');

//TODO: implement CLI setup check
echo "phancap can only be used in the browser\n";
exit(1);
__HALT_COMPILER();
?>
