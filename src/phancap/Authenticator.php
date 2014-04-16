<?php
/**
 * Part of phancap
 *
 * PHP version 5
 *
 * @category  Tools
 * @package   Authenticator
 * @author    Christian Weiske <cweiske@cweiske.de>
 * @copyright 2014 Christian Weiske
 * @license   http://www.gnu.org/licenses/agpl.html GNU AGPL v3
 * @link      http://cweiske.de/phancap.htm
 */
namespace phancap;

/**
 * Authentication helper methods
 *
 * @category  Tools
 * @package   Authenticator
 * @author    Christian Weiske <cweiske@cweiske.de>
 * @copyright 2014 Christian Weiske
 * @license   http://www.gnu.org/licenses/agpl.html GNU AGPL v3
 * @version   Release: @package_version@
 * @link      http://cweiske.de/phancap.htm
 */
class Authenticator
{
    /**
     * Validate the authentication signature.
     *
     * @param object $config Phancap configuration
     *
     * @return void
     * @throws \Exception When a parameter is missing, or authentication fails
     */
    public function authenticate(Config $config)
    {
        if ($config->access === false) {
            throw new \Exception('Authentication not setup');
        }
        if ($config->access === true) {
            //Access without restrictions allowed
            return;
        }

        if (!isset($_GET['atoken'])) {
            throw new \Exception('Parameter missing: atoken');
        }
        if (!isset($_GET['asignature'])) {
            throw new \Exception('Parameter missing: asignature');
        }
        if (!isset($_GET['atimestamp'])) {
            throw new \Exception('Parameter missing: atimestamp');
        }

        $token = $_GET['atoken'];
        if (!array_key_exists($token, $config->access)) {
            throw new \Exception('Unknown atoken');
        }

        $timestamp = (int) $_GET['atimestamp'];
        if ($timestamp + $config->timestampMaxAge < time()) {
            throw new \Exception('atimestamp too old');
        }

        $signature = $_GET['asignature'];

        $params = $_GET;
        unset($params['asignature']);
        $sigdata = $this->getSignatureData($params);

        $verifiedSignature = hash_hmac('sha1', $sigdata, $config->access[$token]);
        if ($signature !== $verifiedSignature) {
            throw new \Exception('Invalid signature');
        }
    }

    /**
     * Convert a list of parameters into a string that can be hashed.
     *
     * @param array $params Parameters, key-value pairs
     *
     * @return string Line of encoded parameters
     */
    protected function getSignatureData($params)
    {
        ksort($params);
        $encparams = array();
        foreach ($params as $key => $value) {
            $encparams[] = $key . '=' . rawurlencode($value);
        }
        return implode('&', $encparams);
    }
}
?>
