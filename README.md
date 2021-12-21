# Auth component for uniondrug

## 安装
1. composer安装
    ```shell
    $ cd project-home
    $ composer require uniondrug/auth
    ```
1. 修改 `app.php` 配置文件
    ```php
    return [
        'default' => [
            ......
            'providers'           => [
                ......
                \Uniondrug\Middleware\MiddlewareServiceProvider::class,
                \Uniondrug\Auth\AuthServiceProvider::class,
            ],
        ],
    ];
    ```
1. 设置`config/auth.php`配置文件, 从`vendor/uniondrug/auth/auth.php`中复制即可
    ```php
    <?php
    return [
        'default' => [
            //token存储的redis链接信息
            'redis' => [
                'options' => [
                    'host' => '192.168.3.193',
                    'auth' => "uniondrug@123",
                    'port' => '6379',
                    'timeout' => 10,
                    'index' => 0
                ]
            ],
            //设置签名验证的public key路径(绝对路径！)，public key文件从vendor/uniondrug/auth/public.key中复制即可
            //phar包部署时要注意.key文件默认不会被打包，要修改项目的PharCommand.php文件！！！
            'public_key_path' => '',
            //白名单，这个列表内的地址不需要认证，通常放登录接口等地址
            'whitelist' => ''
        ],
        'development' => [
            //token存储的redis链接信息
            'redis' => [
                'options' => [
                    'host' => '',
                    'auth' => '',
                    'port' => '',
                    'timeout' => '',
                    'index' => '' 
                ]
            ],
        ],
        'testing' => [
             //token存储的redis链接信息
            'redis' => [
                'options' => [
                    'host' => '',
                    'auth' => '',
                    'port' => '',
                    'timeout' => '',
                    'index' => ''
                ]
            ],
        ],
        'release' => [
        //token存储的redis链接信息
            'redis' => [
                'options' => [
                    'host' => '',
                    'auth' => '',
                    'port' => '',
                    'timeout' => '',
                    'index' => ''
                ]
            ],
        ],
        'production' => [
            //token存储的redis链接信息
            'redis' => [
                'options' => [
                    'host' => '',
                    'auth' => '',
                    'port' => '',
                    'timeout' => '',
                    'index' => ''
                ]
            ],
        ]
    ];
    ```
1. 设置中间件配置文件 `middleware.php` 
    ```php
    return [
        'default' => [
            'middlewares' => [
                ...
                // 注册中间件
                'auth' => \Uniondrug\Auth\AuthMiddleware::class,
            ],
    
            'global'      => [
                ...
                // 将中间件放在全局中间列表中
                'auth'
            ],
    
            ...
        ],
    ];
    ```
## 使用
* 获取通过验证的用户
```php
$_SERVER['member']
```
