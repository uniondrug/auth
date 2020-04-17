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

    /**
     * @var AuthMemberStruct
     */
    public $member = null;

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

    public function checkToken($token)
    {
        list($headerEncoded, $payloadEncoded, $signatureEncoded) = explode('.', $token);
        $dataEncoded = "$headerEncoded.$payloadEncoded";
        $signature = $this->base64url_decode($signatureEncoded);
        $publicKeyResource = openssl_pkey_get_public(file_get_contents($this->config->path('auth.public_key_path')));
        $result = openssl_verify($dataEncoded, $signature, $publicKeyResource, 'sha256');
        if ($result === -1){
            throw new \Exception("Failed to verify signature: ".openssl_error_string());
        }
        elseif ($result){
            $payload = json_decode(base64_decode($payloadEncoded), true);
            $key = $payload['version']['key'];
            $version = $this->getRedis()->get($key);
            if ($version == $payload['version']['value']){
                $this->member = AuthMemberStruct::factory($payload);
                return true;
            }
            else{
                return false;
            }
        }
        else{
            return false;
        }
    }

    public function logout()
    {
        $key = $this->member->version->key;
        $this->getRedis()->del($key);
    }

    /**
     * @return \Redis
     */
    protected function getRedis()
    {
        if (!$this->redis){
            $redisConfig = $this->config->path('auth.redis');
            $this->redis = new Client($redisConfig->toArray());
        }
        return $this->redis;
    }

    protected function base64url_decode($data)
    {
        return base64_decode(str_replace(['-','_'], ['+','/'], $data));
    }
}
