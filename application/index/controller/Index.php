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
        // 查询文章列表数据
        $article = new Article();
        $article->getArtList();

        $this->view->assign('title','网站首页');
        $this->view->assign('empty','当前暂没有数据');
        return $this->view->fetch();
    }

}
