<?php


namespace app\common\model;


use think\Model;

class UserAdmin extends Model
{
    protected $table = 'zh_user_admin';
    protected $pk = 'id';

    protected $autoWriteTimestamp = true;
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';
    protected $dateFormat = 'Y年m月d日';

    // 新增自动完成
    protected $insert = ['is_admin'=>0,'status'=>1];

}