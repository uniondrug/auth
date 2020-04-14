<?php
namespace Uniondrug\TokenAuthMiddleware;

use Phalcon\Config;
use Uniondrug\Auth\AuthStruct;
use Uniondrug\Redis\Client;
use Phalcon\Http\RequestInterface;
use Uniondrug\Framework\Services\Service;

class AuthService extends Service
{
    /**
     * @var array
     */
    protected $whiteList = null;

    protected $redis = null;

    public function getTokenFromRequest(RequestInterface $request)
    {
        $authHeader = $request->getHeader('Authorization');
        preg_match("/^Bearer\s+([_a-zA-Z0-9\-]+)$/", $authHeader, $matches);
        return $matches[1];
    }

    /**
     * 检查URL是否在白名单中
     * @param string $uri
     * @return bool
     */
    public function isWhiteList($uri)
    {
        $regexp = $this->getWhiteList();
        if ($regexp !== ''){
            return preg_match($regexp, preg_replace("/\?(\S*)/", "", $uri)) > 0;
        }
        return false;
    }

    /**
     * 读取白名单的Regexp过滤规则
     * @return string
     */
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
     * 检查Token是否存在
     * @param string $token
     * @return false|AuthStruct
     */
    public function checkToken($token)
    {
        if ($data = $this->getRedis()->get($token)) {
            $data = json_decode($data, true);
            return AuthStruct::factory($data);
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
