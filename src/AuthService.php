<?php
namespace Uniondrug\TokenAuthMiddleware;

use Phalcon\Config;
use Uniondrug\Redis\Client;
use Phalcon\Http\RequestInterface;
use Uniondrug\Auth\AuthMemberStruct;
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
        return $matches[1];
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
        if ($data = $this->getRedis()->get($token)) {
            $data = json_decode($data, true);
            $this->member =  AuthMemberStruct::factory($data);
            return true;
        }
        return false;
    }

    protected function getRedis()
    {
        if (!$this->redis){
            $redisConfig = $this->config->path('auth.redis');
            $this->redis = new Client($redisConfig);
        }
        return $this->redis;
    }
}
