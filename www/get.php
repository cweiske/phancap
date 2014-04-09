<?php
namespace phancap;
/**
 * Get a screenshot for a website.
 */
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

$rep = new Repository();
$rep->setConfig($config);
try {
    $img = $rep->getImage($options);
    if ($config->redirect) {
        header('HTTP/1.0 302 Found');
        header('Expires: ' . date('r', $img->getExpiryDate($options)));
        header('Location: ' . $img->getUrl());
    } else {
        header('Content-type: ' . $img->getMimeType());
        readfile($img->getPath());
    }
} catch (\Exception $e) {
    //FIXME: handle 404s and so properly
    header('HTTP/1.0 500 Internal Server error');
    header('Content-type: text/plain');
    echo $e->getMessage() . "\n";
    exit(2);
}
?>
