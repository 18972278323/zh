<?php


namespace app\common\model;

use think\Model;

class User extends Model
{
    // 指定映射的表名
    protected $table = 'zh_user';
    // 指定表的主键
    protected $pk = 'id';


    // 开启自动写入时间戳
    protected $autoWriteTimestamp = true;
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';
    // protected $dataFormat = 'Y年m月d日';

    // 新增自动完成
    protected $insert = ['status'=>1];
    // 修改自动完成

}