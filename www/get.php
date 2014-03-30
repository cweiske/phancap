<?php
namespace phancap;
/**
 * Get a screenshot for a website.
 */
if (file_exists(__DIR__ . '/../src/phancap/Autoloader.php')) {
    include_once __DIR__ . '/../src/phancap/Autoloader.php';
    Autoloader::register();
} else {
    include_once 'phancap/Autoloader.php';
}

$options = new Options();
try {
    $options->parse($_GET);
} catch (\InvalidArgumentException $e) {
    header('HTTP/1.0 400 Bad Request');
    header('Content-type: text/plain');
    echo $e->getMessage();
    exit(1);
}

var_dump($options->values);
?>
