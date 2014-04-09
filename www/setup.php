<?php
namespace phancap;
/**
 * Check if everything is setup
 */
header('HTTP/1.0 500 Internal Server Error');

if (file_exists(__DIR__ . '/../src/phancap/Autoloader.php')) {
    include_once __DIR__ . '/../src/phancap/Autoloader.php';
    Autoloader::register();
} else {
    include_once 'phancap/Autoloader.php';
}

$messages = array();

$config = new Config();
try {
    $config->load();
    $messages[][] = array('ok', 'Base configuration is ok');

    if ($config->access === true) {
        $messages[][] = array('ok', 'Everyone may access the API');
    } else if ($config->access === false) {
        $messages[][] = array('err', 'API access is disabled');
    } else {
        $messages[][] = array(
            'ok',
            count($config->access) . ' users may access the API'
        );
    }
} catch (\Exception $e) {
    $messages[][] = array('err', $e->getMessage());
}

$adapter = array(
    'Cutycapt'
);
foreach ($adapter as $classpart) {
    $class = '\\phancap\\Adapter_' . $classpart;
    $adapter = new $class();
    $adapter->setConfig($config);
    $errors = $adapter->isAvailable();
    if ($errors === true) {
        $messages[][] = array(
            'ok', 'Adapter ' . $classpart . ' is available'
        );
    } else {
        foreach ($errors as $msg) {
            $messages['Adapter: '. $classpart][] = array('err', $msg);
        }
    }
}

header('HTTP/1.0 200 OK');

$out = <<<HTM
<?xml version="1.0" encoding="utf-8"?>
<html>
 <head>
  <title>phancap setup check</title>
  <style type="text/css">
    li.ok:before {
        content: '✔';
        color: green;
        padding: 0 0.5ex;
        margin-right: 0.5ex;
    }
    li.err:before {
        content: "✘";
        color: white;
        background-color: red;
        padding: 0 0.5ex;
        margin-right: 0.5ex;
    }
  </style>
 </head>
 <body>
  <h1>phancap setup check</h1>
<ul>
HTM;
foreach ($messages as $key => $messages) {
    if (!is_numeric($key)) {
        $out .= '<li>' . htmlspecialchars($key)
            . '<ul>';
    }
    foreach ($messages as $data) {
        list($state, $message) = $data;
        $out .= '<li class="' . $state . '">';
        $out .= htmlspecialchars($message);
        $out .= '</li>' . "\n";
    }
    if (!is_numeric($key)) {
        $out .= '</ul></li>' . "\n";
    }
}
$out .= <<<HTM
  </ul>
  <p>
   <a href="./">back</a> to the index
  </p>
 </body>
</html>
HTM;
echo $out;
?>
