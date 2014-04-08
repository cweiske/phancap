<?php
namespace phancap;

class Authenticator
{
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
