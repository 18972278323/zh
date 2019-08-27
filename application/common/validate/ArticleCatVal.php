<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/8/27
 * Time: 15:23
 */

namespace app\common\validate;


use think\Validate;

class ArticleCatVal extends Validate
{
    protected  $rule = [
        'name|分类名称'         => 'require',
        'sort|排序序号'         => 'require',
    ];

}