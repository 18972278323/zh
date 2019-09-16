<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/9/16
 * Time: 11:04
 */

namespace app\index\controller;


use app\common\controller\Base;
use think\facade\Request;
use app\common\model\Comment as CommentModel;

class Comment extends Base
{
    // 添加评论
    public function add(){
        $data = Request::param();
        $res = CommentModel::create($data);
        if($res){
            return ['status'=>1,'message'=>'评论成功'];
        }
    }
}