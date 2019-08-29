<?php
/**
 * 基础控制器
 */

namespace app\common\controller;

use app\common\model\ArticleCat;
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
        $this->getCateList();
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

    /**
     * 动态加载导航栏,在初始化方法中调用
     */
    public function getCateList(){
        $cateList = ArticleCat::all(function($query){
           $query->where('status','=',1)->order('sort','asc');
        });

        $this->view->assign('cateList',$cateList);
    }
}