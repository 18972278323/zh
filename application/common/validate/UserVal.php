<?php


namespace app\common\validate;

use think\Validate;

class UserVal extends Validate
{
    // 定义用户的验证规则
    public $rule = [
        'name|姓名' =>[
            'require'   => 'require',
            'length'    => '2,10',
            'chsAlphaNum' => 'chsAlphaNum',
        ],
        'email|邮箱'=>[
            'require'   => 'require',
            'email'     => 'email',
            'unique'    => 'zh_user',  // 在zh_user表中验证唯一性
        ],
        'password|密码' =>[
            'require'   => 'require',
            'length'    => '3,12',
            'alphaNum'  => 'alphaNum',
            'confirm'   => 'password_confirm',
        ],
        'mobile|手机'=>[
            'require'   => 'require',
            'mobile'    => 'mobile',
            'number'    => 'number',
            'unique'    => 'zh_user',
        ],
    ];
}