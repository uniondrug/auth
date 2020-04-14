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
                'host' => '192.168.3.193',
                'auth' => "uniondrug@123",
                'port' => '6379',
                'timeout' => 10,
                'index' => 0
            ],
            //白名单，这个列表内的地址不需要认证，通常放登录接口等地址
            'whitelist' => ''
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
$this->authService->member
```