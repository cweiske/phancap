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
 </head>
 <body>
  <h1>phancap</h1>
  <p>
   Web service to create website screenshots.
  </p>
  <form method="get" action="./get.php">
    <fieldset>
      <legend>Create website screenshot</legend>
      <label for="url">URL:</label>
      <input type="text" name="url" id="url" size="30"/>
      <button type="submit">Go</button>
    </fieldset>
  </form>

  <h2>API</h2>
  <p>
   The API is accessible at <a href="get.php">get.php</a>.
  </p>
  <table border="1">
   <caption>Available URL parameters</caption>
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


  <h2>Tools</h2>
  <ul>
   <li>
    <a href="setup.php">Setup check</a> to test if everything is ok
   </li>
  </ul>
 </body>
</html>
