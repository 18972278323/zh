<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/8/27
 * Time: 15:53
 */

namespace app\index\controller;


use app\common\controller\Base;
use app\common\model\ArticleCat;
use think\facade\Request;

class Article extends Base
{
    public function insert(){
        if(Request::isGet()){ // 判断登录，查询分类，分配数据，跳转页面
            $this->isLogin();

            $this->view->assign('title','发布文章');

            $cateList = ArticleCat::all()->order('sort','asc');
            if(count($cateList)>0 ){
                $this->assign('cateList',$cateList);
            }else{
                return $this->redirect(url('ArticleCate/add'));
            }

            return $this->view->fetch();
        }else{ // 保存数据

        }
    }
}