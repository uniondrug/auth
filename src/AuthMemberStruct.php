<?php

namespace Uniondrug\Auth;

use Uniondrug\Structs\Struct;

class AuthMemberStruct extends Struct
{
    /**
     * 药联用户id
     *
     * @var string
     */
    public $memberId;

    /**
     * 绑定微信openid
     *
     * @var string
     */
    public $wxOpenid;

    /**
     * jwt版本信息（用来验证有效性）
     *
     * @var \Uniondrug\Auth\AuthVersion
     */
    public $version;


    /**
     * 登录的用户信息
     * 注意，用户登录期间可能发生修改
     *
     * @var \Uniondrug\Auth\AuthMemberInfo
     */
    public $info;

    /**
     * 用户的渠道信息
     *
     * @var \Uniondrug\Auth\AuthChannel
     */
    public $channel;
}

class AuthVersion extends Struct
{
    /**
     * redis的key
     *
     * @var string
     */
    public $key;

    /**
     * redis的value
     *
     * @var int
     */
    public $value;
}

class AuthMemberInfo extends Struct
{
    /**
     * 姓名
     *
     * @var string
     */
    public $name;

    /**
     * 手机号
     *
     * @var string
     */
    public $mobile;
}

class AuthChannel extends Struct
{
    /**
     * 渠道类型：wechat——微信；QJB——企建部；GHT——工会通；WANDA——万达；qywechat——企业微信；
     */
    public $type;
    /**
     * 微信openid
     *
     * @var string
     */
    public $openid;
    /**
     * 万达openid
     *
     * @var string
     */
    public $wdOpenid;
    /**
     * 万达account id
     *
     * @var string
     */
    public $wdAccountId;
    /**
     * 企业微信openid
     *
     * @var string
     */
    public $qyOpenid;
}
