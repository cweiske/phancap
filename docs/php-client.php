<?php
/**
 * Example phancap API client written in PHP.
 * Mainly to demonstrate the authentication.
 *
 * @author Christian Weiske <cweiske@cweiske.de>
 */

//phancap service configuration
$phancapConfig = array(
    'url'    => 'http://example.org/phancap.phar/get.php',
    'token'  => 'demo',
    'secret' => 'coho8ajj2as'
);

//fetch the screenshot URL
$screenshotUrl = getScreenshotUrl('http://example.org/', $phancapConfig);

//output an image tag with the screenshot url
echo '<img src="' . htmlspecialchars($screenshotUrl) . '"'
    . ' alt="Screenshot for example.org/" />' . "\n";

/**
 * Creates an URL for a website screenshot.
 * Automatically adds a authentication signature for phancap.
 *
 * @param string $websiteUrl    URL to website from which the screenshot
 *                              shall be generated
 * @param array  $phancapConfig Configuration array. Supported Keys:
 *                              - url: Full URL to phancap's get.php
 *                              - token: Username
 *                              - secret: Password for the username
 *
 * @return string URL for the website screenshot
 */
function getScreenshotUrl($websiteUrl, $phancapConfig)
{
    //default parameters for the phancap service
    $parameters = array(
        'url' => $websiteUrl,
        'swidth' => 300,
        'sformat' => 'jpg',
    );

    if (isset($phancapConfig['token'])) {
        $parameters['atoken']     = $phancapConfig['token'];
        $parameters['atimestamp'] = time();

        //create signature
        ksort($parameters);
        foreach ($parameters as $key => $value) {
            $encparams[] = $key . '=' . rawurlencode($value);
        }
        $encstring = implode('&', $encparams);
        $signature = hash_hmac('sha1', $encstring, $phancapConfig['secret']);
        //append signature to parameters
        $parameters['asignature'] = $signature;
    }

    //url-encode the parameters
    $urlParams = array();
    foreach ($parameters as $key => $value) {
        $urlParams[] = $key . '=' . urlencode($value);
    }

    //final URL
    return $phancapConfig['url'] . '?' . implode('&', $urlParams);
}
?>