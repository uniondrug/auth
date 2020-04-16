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

    public $member = null;

    public function getTokenFromRequest(RequestInterface $request)
    {
        $authHeader = $request->getHeader('Authorization');
        preg_match("/^Bearer\s+([_a-zA-Z0-9\-]+)$/", $authHeader, $matches);
        return $matches[1]??'';
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
        $signature = base64_decode($signatureEncoded);
        $publicKeyResource = openssl_pkey_get_public(__DIR__.'public.key');
        $result = openssl_verify($dataEncoded, $signature, $publicKeyResource, 'sha256');
        if ($result === -1){
            throw new \Exception("Failed to verify signature: ".openssl_error_string());
        }
        elseif ($result){
            $payload = json_decode(base64_decode($payloadEncoded), true);
            $channel = $payload['channel'];
            $memberId = $payload['memberId'];
            $key = $channel?"AUTH_{$channel['type']}_{$memberId}":"AUTH_mobile_{$memberId}";
            $flag = $this->getRedis()->get($key);
            if ($flag == $payload['flag']){
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

    protected function getRedis()
    {
        if (!$this->redis){
            $redisConfig = $this->config->path('auth.redis');
            $this->redis = new Client($redisConfig->toArray());
        }
        return $this->redis;
    }
}
