<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/9/16
 * Time: 12:02
 */

namespace app\common\model;


use think\Model;

class Comment extends Model
{
    protected $pk = 'id';
    protected $table = 'zh_user_comments';

    protected $autoWriteTimestamp = true;
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';

    protected $insert = ['status'=>1,'create_time'];
    protected $update = ['update_time'];
}