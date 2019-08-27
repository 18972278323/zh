<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/8/27
 * Time: 15:04
 */

namespace app\common\model;


use think\Model;

class Article extends Model
{
    protected $pf = 'id';
    protected $table = 'zh_article';

    protected $autoWriteTimestamp = true;
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';

    // 新增自动完成
    protected $insert = ['is_hot'=>0,'is_top'=>0,'pv'=>0,'status'=>1,'create_time'];

    protected $update = ['update_time'];

}