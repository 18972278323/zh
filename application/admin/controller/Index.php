<?php


namespace app\admin\controller;

use app\admin\controller\Base;

class Index extends Base
{
    /**
     * 跳转到主页
     * @return string
     * @throws \think\Exception
     */
    public function index(){
        // 判断是否登录
        $this->isLogin();

        return $this->view->fetch('index',['title'=>'后台主页']);
    }
}