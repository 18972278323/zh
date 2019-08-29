<?php
/**
 * 基础控制器
 */

namespace app\common\controller;

use think\Controller;
use think\facade\Session;

class Base extends Controller
{
    /**
     * 初始化方法
     * 创建常量,公共方法
     *
     */
    protected function initialize()
    {

    }

    /**
     * 判断用户是否重复登录
     */
    public function logined(){
        $res = Session::has('id');

        if($res){
            $this->redirect(url('Index/index'));
        }
    }


    /**
     * 判断用户是否已经登录
     */
    public function isLogin(){
        $res = Session::has('id');

        if(!$res){
            $this->redirect(url('User/login'));
        }
    }


}