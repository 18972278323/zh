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
use app\common\model\Article as ArticleModel;
use think\Db;
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
        }else if(Request::isPost()){ // 保存数据
            $data = (Request::post());
            $articleRule = "app\\common\\validate\\ArticleVal";
            $res = $this->validate($data,$articleRule);

            if($res !== true){
                echo '<script>alert("'.$res.'");window.history.back(-1);</script>';
            }else{ // 验证通过,处理文件上传,获取文件路径
                $img = Request::file('title_img');

                if($img){
                    $uploadRes = $img->validate([
                        'size'=>50000000,
                        'ext'=>'jpg,png,jpeg,gif'
                    ])->move('uploads');

                    if($uploadRes){
                        $data['title_img'] = $uploadRes->getSaveName();
                    }else{
                        $this->error('文件上传失败');
                    }

                    // 保存信息
                    $saveRes = ArticleModel::create($data);
                    if($saveRes){
                        $this->redirect(url('Index/index'));
                    }else{
                        $this->error('文章发布失败');
                    }
                }
            }
        }
    }


    /**
     * 获取指定分类的文章列表
     */
    public function getArtList(){
        if(Request::isGet()){
            $cateId = Request::param('cate_id');
            if($cateId){
                $artList = Db::table('zh_article')
                        ->where('cate_id','=',$cateId)
                        ->where('status','=',1)
                        ->order('create_time','desc')
                        ->paginate(3);
            }else{
                $artList = Db::table('zh_article')
                    ->where('status','=',1)
                    ->order('create_time','asc')
                    ->paginate(3);
            }

            $this->view->assign('artList', $artList);
            $this->view->assign('title', '文章列表');
            return $this->view->fetch('index/index');

        }
    }
}