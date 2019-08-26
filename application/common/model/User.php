<?php


namespace app\common\model;

use think\Model;

class User extends Model
{
    // 指定映射的表名
    protected $table = 'zh_user';
    // 指定表的主键
    protected $pk = 'id';
}