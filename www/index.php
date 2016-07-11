<?php
/**
 * Give information about phancap 
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
header('HTTP/1.0 200 OK');

$options = new Options();
$config = new Config();
try {
    $config->load();
    $options->setConfig($config);
} catch (\Exception $e) {
}
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" lang="en">
 <head>
  <title>phancap</title>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
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

     <div class="row">
      <div class="col-md-6">
       <p>
        Web service to create website screenshots.
       </p>

      </div>
      <div class="col-md-6">

       <div class="panel panel-default">
        <div class="panel-heading">Create screenshot</div>
        <div class="panel-body">
         <?php if ($config->access === false) { ?>
          <div class="alert alert-danger">API is disabled</div>
         <?php } else if ($config->access !== true) { ?>
          <div class="alert alert-warning">API requires authentication</div>
         <?php } ?>
         <form method="get" action="./get.php" class="form-inline">
          <div class="form-group">
           <label for="url">URL:</label>
           <input type="text" name="url" id="url" class="form-control"
                  placeholder="http://example.org/" />
          </div>
          <button type="submit" class="btn btn-default">Go</button>
         </form>
        </div>
       </div>

      </div>
     </div>


     <h2 id="tools">Tools</h2>
     <ul class="list-group">
      <li class="list-group-item">
       <a href="setup.php">Setup check</a> to test if everything is ok
      </li>
      <li class="list-group-item">
       <a href="README.html">README</a>
      </li>
     </ul>


     <h2 id="api">API</h2>
     <p>
      The API is accessible at <a href="get.php">get.php</a>.
     </p>

     <div class="panel panel-default">
      <div class="panel-heading" style="text-align: center">
       Available URL parameters
      </div>
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
foreach ($options->options as $name => $option) {
    echo '<tr>'
        . '<td><var>' . $name . '</var></td>'
        . '<td>' . htmlspecialchars($option['title']) . '</td>'
        . '<td>'
        . (
            is_array($option['type'])
            ? ('One of: <kbd>' . implode('</kbd>, <kbd>', $option['type']) . '</kbd>')
            : str_replace('skip', '&#160;', $option['type'])
        )
        . '</td>'
        . '<td>&#160;<kbd>' . $option['default'] . '</kbd></td>'
        . '</tr>';
}
?>
       </tbody>
      </table>
     </div>
     <p>
      Ages can be given as ISO 8601 duration specification, for example:
     </p>
     <dl class="dl-horizontal">
      <dt><kbd>P1Y</kbd></dt><dd>1 year</dd>
      <dt><kbd>P2W</kbd></dt><dd>2 weeks</dd>
      <dt><kbd>P1D</kbd></dt><dd>1 day</dd>
      <dt><kbd>PT4H</kbd></dt><dd>4 hours</dd>
     </dl>


     <h2 id="complex">Complex request</h2>
     <form method="get" action="./get.php" class="form-horizontal">
      <div class="form-group">
       <label for="url2" class="col-sm-3 control-label">URL</label>
       <div class="col-sm-9">
        <input type="text" name="url" id="url2" class="form-control"
               placeholder="http://example.org/" />
       </div>
      </div>

      <div class="form-group">
       <label class="col-sm-3 control-label">Mode</label>
       <div class="col-sm-9">
        <label class="radio-inline">
         <input type="radio" name="smode" value="screen" checked="checked"/> screen
        </label>
        <label class="radio-inline">
         <input type="radio" name="smode" value="page"/> page
        </label>
       </div>
      </div>

      <div class="form-group">
       <label class="col-sm-3 control-label">Format</label>
       <div class="col-sm-9">
        <label class="radio-inline">
         <input type="radio" name="sformat" value="png" checked="checked"/> png
        </label>
        <label class="radio-inline">
         <input type="radio" name="sformat" value="jpg"/> jpg
        </label>
        <label class="radio-inline">
         <input type="radio" name="sformat" value="pdf"/> pdf
        </label>
       </div>
      </div>

      <div class="form-group">
       <label class="col-sm-3 control-label">Browser size</label>
       <div class="col-sm-9">
        <div class="row">
         <div class="col-xs-3">
          <input type="number" name="bwidth" class="form-control"
                 placeholder="width"/>
         </div>
         <div class="col-xs-3">
          <input type="number" name="bheight" class="form-control"
                 placeholder="height"/>
         </div>
        </div>
       </div>
      </div>

      <div class="form-group">
       <label class="col-sm-3 control-label">Screenshot size</label>
       <div class="col-sm-9">
        <div class="row">
         <div class="col-xs-3">
          <input type="number" name="swidth" class="form-control"
                 placeholder="width"/>
         </div>
         <div class="col-xs-3">
          <input type="number" name="sheight" class="form-control"
                 placeholder="height"/>
         </div>
        </div>
       </div>
      </div>

      <div class="form-group">
       <label for="smaxage" class="col-sm-3 control-label">Maximum age</label>
       <div class="col-sm-9">
        <div class="row">
         <div class="col-xs-2">
          <input type="text" name="smaxage" id="smaxage" class="form-control"
                 placeholder="P1Y" />
         </div>
        </div>
       </div>
      </div>

      <div class="form-group">
       <div class="col-sm-offset-3 col-sm-9">
        <button type="submit" class="btn btn-primary">Create screenshot</button>
       </div>
      </div>
     </form>

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
