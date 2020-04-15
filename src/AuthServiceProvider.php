<?php

namespace Uniondrug\Auth;

use Phalcon\Di\ServiceProviderInterface;

class AuthServiceProvider implements ServiceProviderInterface
{
    public function register(\Phalcon\DiInterface $di)
    {
        $di->set(
            'authService',
            function () {
                return new AuthService();
            }
        );
    }
}
