<?php
/**
 * Create a website screenshot API
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

header('HTTP/1.0 500 Internal Server Error');

if (file_exists(__DIR__ . '/../src/phancap/Autoloader.php')) {
    include_once __DIR__ . '/../src/phancap/Autoloader.php';
    Autoloader::register();
} else {
    include_once 'phancap/Autoloader.php';
}

$config = new Config();
$config->load();

$options = new Options();
try {
    $options->setConfig($config);
    $options->parse($_GET);
} catch (\InvalidArgumentException $e) {
    header('HTTP/1.0 400 Bad Request');
    header('Content-type: text/plain');
    echo $e->getMessage() . "\n";
    exit(1);
}

$auth = new Authenticator();
try {
    $auth->authenticate($config);
} catch (\Exception $e) {
    header('HTTP/1.0 401 Unauthorized');
    header('Content-type: text/plain');
    echo $e->getMessage() . "\n";
    exit(1);
}

if (strpos($_SERVER['HTTP_USER_AGENT'], ' cutycapt ') !== false) {
    //phancap is trying to render a page with an image that
    // is being fetched from phancap itself, leading to an
    // infinite loop
    header('HTTP/1.0 500 Infinite Loop');
    header('Location: ' . $config->getCurrentUrlDir() . '/infinite.png');
    exit(3);
}

$rep = new Repository();
$rep->setConfig($config);
try {
    $img = $rep->getImage($options);
    if ($config->redirect) {
        header('HTTP/1.0 302 Found');
        header('phancap-file: ' . $img->name);
        header('Expires: ' . date('r', $img->getExpiryDate($options)));
        header('Location: ' . $img->getUrl());
    } else {
        header('HTTP/1.0 200 OK');
        header('phancap-file: ' . $img->name);
        header('Content-Type: ' . $img->getMimeType());
        header('Content-Length: ' . filesize($img->getPath()));
        readfile($img->getPath());
    }
} catch (\Exception $e) {
    //FIXME: handle 404s and so properly
    //FIXME: send out error image if images are preferred
    header('HTTP/1.0 500 Internal Server error');
    header('Content-type: text/plain');
    echo $e->getMessage() . "\n";
    exit(2);
}
?>
