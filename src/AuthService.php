<?php
namespace Uniondrug\Auth;

use Phalcon\Config;
use Uniondrug\Redis\Client;
use Phalcon\Http\RequestInterface;
use Uniondrug\Framework\Services\Service;

class AuthService extends Service
{
    protected $whiteList = null;

    protected $redis = null;

    public function getTokenFromRequest(RequestInterface $request)
    {
        $authHeader = $request->getHeader('Authorization');
        return trim(str_replace('Bearer', '', $authHeader));
    }

    public function isWhiteList($uri)
    {
        $regexp = $this->getWhiteList();
        if ($regexp !== ''){
            return preg_match($regexp, preg_replace("/\?(\S*)/", "", $uri)) > 0;
        }
        return false;
    }

    public function getWhiteList()
    {
        $whiteList = $this->config->path('auth.whitelist');
        if (is_string($whiteList) && $whiteList !== ''){
            return $whiteList;
        } else {
            return '';
        }
    }

    /**
     * @param $token
     * @return AuthMemberStruct|bool
     * @throws \Exception
     */
    public function checkToken($token,$accEnv=null)
    {
        $token = explode('.', $token);
        if (count($token) != 3) return false;
        list($headerEncoded, $payloadEncoded, $signatureEncoded) = $token;
        $dataEncoded = "$headerEncoded.$payloadEncoded";
        $signature = $this->base64url_decode($signatureEncoded);
        $publicKeyResource = openssl_pkey_get_public(file_get_contents($this->config->path('auth.public_key_path')));
        $result = openssl_verify($dataEncoded, $signature, $publicKeyResource, 'sha256');
        openssl_pkey_free($publicKeyResource);
        if ($result === -1){
            throw new \Exception("Failed to verify signature: ".openssl_error_string());
        }
        elseif ($result){
            $payload = json_decode($this->base64url_decode($payloadEncoded), true);
            if($accEnv){
                if(!$this->checkAccEnv($payload,$accEnv)) return  false;
            }
            $key = $payload['version']['key'];
            $version = $this->getRedis()->get($key);
            return $version == $payload['version']['value'] ? $payload : false;
        }
        else{
            return false;
        }
    }

    /*
     * 检查token内accEnv是否包含当前平台accEnv
     */
    private function checkAccEnv($payload,$accEnv)
    {
        $type = $payload['channel']['type'];
        $accEnv = $accEnv."_".$type;
        $existAccEnv = $payload['channel']['accEnvs'];
        return in_array($accEnv,$existAccEnv);
    }

    /**
     * @return \Redis
     */
    protected function getRedis()
    {
        if (!$this->redis){
            $redisConfig = $this->config->path('auth.redis.options');
            $this->redis = new Client($redisConfig->toArray());
        }
        return $this->redis;
    }

    protected function base64url_decode($data)
    {
        return base64_decode(str_replace(['-','_'], ['+','/'], $data));
    }

    /**
     * openssl解密openid
     * @param $encryptedOpenid
     * @return mixed
     * @throws \Exception
     */
    public function opensslDecryptOpenid($encryptedOpenid)
    {
        $publicKeyFilePath = $this->config->path('auth.public_key_path');
        $publicKey = openssl_pkey_get_public(file_get_contents($publicKeyFilePath));
        $ret = openssl_public_decrypt(base64_decode($encryptedOpenid), $decryptData, $publicKey);
        if(empty($ret)){
            throw new \Exception("openssl decrypt fail ".openssl_error_string());
        }
        openssl_pkey_free($publicKey);
        return $decryptData;
    }
}
