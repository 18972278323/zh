<?php


namespace app\admin\controller;


use think\Controller;
use think\facade\Session;

class Base extends Controller
{

    /**
     * 初始化方法
     */
    public function initialize()
    {

    }


    /**
     * 判断用户是否登录
     */
    public function isLogin(){
        $adminId = Session::get('admin_id');

        if(!$adminId){
            $this->redirect(url('admin/User/login'));
        }
    }

    /**
     * 判断用户是否重复登录
     */
    public function logined(){
        $adminId = Session::get('admin_id');

        if($adminId){
            $this->redirect(url('admin/index/index'));
        }
    }

}