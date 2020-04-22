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
    public function checkToken($token)
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
            $key = $payload['version']['key'];
            $version = $this->getRedis()->get($key);
            return $version == $payload['version']['value'] ? AuthMemberStruct::factory($payload) : false;
        }
        else{
            return false;
        }
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
}
