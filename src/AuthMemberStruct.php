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
     * @var \Uniondrug\Auth\AuthChannel
     */
    public $channel;
}

class AuthChannel extends Struct
{
    /**
     * 渠道类型：wechat——微信；QJB——企建部；GHT——工会通；WANDA——万达
     */
    public $type;
    /**
     * 微信openid
     *
     * @var string
     */
    public $openid;
}
