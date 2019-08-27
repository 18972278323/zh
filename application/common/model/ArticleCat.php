<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/8/27
 * Time: 15:20
 */

namespace app\common\model;


use think\Model;

class ArticleCat extends Model
{
    protected $pk = 'id';
    protected $table = 'zh_article_category';

    protected $autoWriteTimestamp = true;
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';

    protected $insert = ['status'=>1,'create_time'];

    protected $update = ['update_time'];

}