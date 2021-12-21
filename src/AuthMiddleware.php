<?php
namespace Uniondrug\Auth;

use Phalcon\Http\RequestInterface;
use Uniondrug\Middleware\Middleware;
use Uniondrug\Middleware\DelegateInterface;

/**
 * Class TokenAuthMiddleware
 * @package Uniondrug\TokenAuthMiddleware
 * @property \Uniondrug\TokenAuthMiddleware\AuthService $tokenAuthService
 */
class AuthMiddleware extends Middleware
{
    public function handle(RequestInterface $request, DelegateInterface $next)
    {
        // 0. WhiteList
        if ($this->authService->isWhiteList($request->getURI())) {
            return $next($request);
        }
        // 1. 提取TOKEN, return 401
        $token = $this->authService->getTokenFromRequest($request);
        if (empty($token)) {
            $this->di->getLogger('auth')->debug(sprintf("[Auth] Unauthorized."));
            return $this->serviceServer->withError('Unauthorized', 401);
        }
        // 2. 校验TOKEN, return 403
        if (!$member = $this->authService->checkToken($token)) {
            $this->di->getLogger('auth')->debug(sprintf("[Auth] Invalid Token: token=%s", $token));
            return $this->serviceServer->withError('Forbidden: Invalid Token', 403);
        }
        $_SERVER['member'] = $member;
        return $next($request);
    }
}
