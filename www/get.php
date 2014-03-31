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
$config->setupCheck();

$options = new Options();
try {
    $options->parse($_GET);
} catch (\InvalidArgumentException $e) {
    header('HTTP/1.0 400 Bad Request');
    header('Content-type: text/plain');
    echo $e->getMessage() . "\n";
    exit(1);
}

$rep = new Repository();
$rep->setConfig($config);
try {
    $img = $rep->getImage($options);
    header('HTTP/1.0 302 Found');
    header('Location: ' . $img->getUrl());
} catch (\Exception $e) {
    //FIXME: handle 404s and so properly
    header('HTTP/1.0 500 Internal Server error');
    header('Content-type: text/plain');
    echo $e->getMessage() . "\n";
    exit(2);
}
?>
