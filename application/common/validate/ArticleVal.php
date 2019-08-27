<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/8/27
 * Time: 15:12
 */

namespace app\common\validate;


use think\Validate;

class ArticleVal extends Validate
{
    protected $rule = [
        'title|文章标题'         => 'require',
        'cate_id|文章分类'       => 'require',
        'user_id|文章作者'       => 'require',
        'content|文章内容'       => 'require',
    ];
}