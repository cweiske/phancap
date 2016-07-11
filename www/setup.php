<?php
/**
 * Check if everything is setup correctly
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

$messages = array();

$config = new Config();
try {
    $config->load();
    if ($config->disableSetup) {
        header('HTTP/1.0 403 Forbidden');
        header('Content-type: text/plain');
        echo "Setup check is disabled.\n";
        exit(1);
    }
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

    foreach ($config->cfgFiles as $cfgFile) {
        $messages[][] = array(
            'info', 'Possible config file: ' . $cfgFile
        );
    }
    if ($config->cfgFileExists) {
        $messages[][] = array(
            'ok', 'Configuration file loaded'
        );
    } else {
        $messages[][] = array(
            'info', 'No configuration file found'
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

if (!function_exists('idn_to_ascii')) {
    $messages[][] = array(
        'err', 'Function "idn_to_ascii" is not available'
    );
}

$out = <<<HTM
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" lang="en">
 <head>
  <title>phancap setup check</title>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
  <link rel="stylesheet" href="css/bootstrap.min.css"/>
  <link rel="stylesheet" href="css/bootstrap-theme.min.css"/>
  <link rel="stylesheet" href="css/phancap.css"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <style type="text/css">
    /*
    li:before {
        text-align: center;
        display: inline-block;
        width: 1em;
        padding: 0 0.5ex;
        margin-right: 0.5ex;
    }
    li.ok:before {
        content: '✔';
        color: green;
    }
    li.err:before {
        content: "✘";
        color: white;
        background-color: red;
    }
    li.info:before {
        content: "i";
        font-weight: bold;
        color: blue;
    }
    */
  </style>
 </head>
 <body>
  <div class="container">
   <div class="row">
    <div class="col-md-2"></div>
    <div class="col-md-8">

     <div class="page-header">
      <h1>phancap setup check</h1>
     </div>

     <ul class="list-group">
HTM;
$stateMap = array(
    'ok' => 'success',
    'info' => 'info',
    'err' => 'danger'
);
foreach ($messages as $key => $messages) {
    if (!is_numeric($key)) {
        $out .= '<li class="list-group-item">' . htmlspecialchars($key)
            . '<ul class="list-group">';
    }
    foreach ($messages as $data) {
        list($state, $message) = $data;
        $out .= '<li class="list-group-item list-group-item-'
            . $stateMap[$state] . '">';
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
    </div>
   </div>
  </div>

  <div class="container footer">
   <a href="http://cweiske.de/phancap.htm">phancap</a>,
   the self-hosted website screenshot service is available under the
   <a href="http://www.gnu.org/licenses/agpl-3.0.html">
    <abbr title="GNU Affero General Public License">AGPL</abbr></a>.
  </div>

 </body>
</html>
HTM;

header('HTTP/1.0 200 OK');
echo $out;
?>
