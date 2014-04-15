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
header('HTTP/1.0 200 OK');
?>
<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
 <head>
  <title>phancap</title>

  <link rel="stylesheet" href="css/bootstrap.min.css"/>
  <link rel="stylesheet" href="css/bootstrap-theme.min.css"/>
  <link rel="stylesheet" href="css/phancap.css"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
 </head>
 <body>
  <div class="container">
   <div class="row">
    <div class="col-md-2"></div>
    <div class="col-md-8">

     <div class="page-header">
      <h1>phancap</h1>
     </div>
     <p>
      Web service to create website screenshots.
     </p>

     <div class="panel panel-default">
      <div class="panel-heading">Create website screenshot</div>
      <div class="panel-body">
       <form method="get" action="./get.php" class="form-inline" role="form">
        <div class="form-group">
         <label for="url">URL:</label>
         <input type="text" name="url" id="url" size="30" class="form-control"
                placeholder="http://example.org/" />
        </div>
        <button type="submit" class="btn btn-default">Go</button>
       </form>
      </div>
     </div>


     <h2 id="api">API</h2>
     <p>
      The API is accessible at <a href="get.php">get.php</a>.
     </p>

     <div class="panel panel-default">
      <div class="panel-heading" style="text-align: center">Available URL parameters</div>
      <table class="table table-striped table-bordered table-condensed">
       <thead>
        <tr>
         <th>Name</th>
         <th>Description</th>
         <th>Type</th>
         <th>Default</th>
        </tr>
       </thead>
       <tbody>
<?php
$options = new Options();
$config = new Config();
try {
    $config->load();
    $options->setConfig($config);
} catch (\Exception $e) {}

foreach ($options->options as $name => $option) {
    echo '<tr>'
        . '<td><tt>' . $name . '</tt></td>'
        . '<td>' . htmlspecialchars($option['title']) . '</td>'
        . '<td>'
        . (
            is_array($option['type'])
            ? ('One of: <tt>' . implode('</tt>, <tt>', $option['type']) . '</tt>')
            : str_replace('skip', '&#160;', $option['type'])
        )
        . '</td>'
        . '<td>&#160;<tt>' . $option['default'] . '</tt></td>'
        . '</tr>';
}
?>
       </tbody>
      </table>
     </div>


     <h2 id="tools">Tools</h2>
     <ul class="list-group">
      <li class="list-group-item">
       <a href="setup.php">Setup check</a> to test if everything is ok
      </li>
     </ul>

    </div>
   </div>
  </div>
 </body>
</html>
