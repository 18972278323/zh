<?php
namespace app\index\controller;

use app\common\controller\Base;
use think\Db;

class Index extends Base
{

    /**
     * 跳转到首页
     * @return string
     * @throws \think\Exception
     */
    public function index()
    {
        $this->view->assign('title','首页');
        return $this->view->fetch();
    }

}
