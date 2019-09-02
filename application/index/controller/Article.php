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
use think\facade\Session;

class Article extends Base
{
    /**
     * 添加文章
     * @return string|void
     * @throws \think\Exception
     */
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
     * 获取文章列表功能 包含搜索
     * @return string
     * @throws \think\Exception
     * @throws \think\exception\DbException
     */
    public function getArtList(){
        if(Request::isGet()){
            // 添加查询条件
            $maps = [];
            $maps[] = ['status','=',1];

            $cateId = Request::param('cate_id');
            if($cateId){
                $maps[] = ['cate_id','=',$cateId];
            }

            $keyword = Request::param('keyword');
            if($keyword){
                $maps[] = ['title','like','%'.$keyword.'%'];
            }

            if($cateId){
                $artList = Db::table('zh_article')
                        ->where($maps)
                        ->order('create_time','desc')
                        ->paginate(3);

                $cateName = ArticleCat::where('id','=',$cateId)->value('name');
                $this->view->assign('cateName', $cateName);
            }else{
                $artList = Db::table('zh_article')
                    ->where($maps)
                    ->order('create_time','desc')
                    ->paginate(3);

                $this->view->assign('cateName', '全部文章');

            }

            $this->view->assign('artList', $artList);
            $this->view->assign('title', '首页');
            return $this->view->fetch('index/index');

        }
    }

    /**
     * 获取文章详情
     * @return mixed
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function detail(){
        $artId = Request::param('id');

        $criteria = [];
        $criteria['art_id'] = $artId;

        // 查询文章详情并分配
        $art = ArticleModel::get($artId);
        if($art){
            $this->view->assign('art',$art);
        }

        // 查询文章的收藏和点赞量
        $criteria['status'] = 1;
        $countFav = Db::table('zh_user_fav')->where($criteria)->count();
        $this->view->assign('countFav', $countFav);

        $countLike = Db::table('zh_user_like')->where($criteria)->count();
        $this->view->assign('countLike', $countLike);

        // 默认向页面分配是否收藏的变量
        $this->view->assign('is_fav',0);
        $this->view->assign('is_like',0);

        // 如果用户登录，查询该文章是否被该用户收藏或点赞, 覆盖以上变量分配
        $userId = Session::get('id');
        if($userId){
            $criteria['user_id'] = $userId;

            $is_fav = Db::table('zh_user_fav')->where($criteria)->field('status')->find();
            $is_like = Db::table('zh_user_like')->where($criteria)->field('status')->find();

            if($is_fav['status']){ // null 或者 0 表示未收藏
                $this->view->assign('is_fav',1);
            }else{
                $this->view->assign('is_fav',0);
            }

            if($is_like['status']){ // null 或者 0 表示未点赞
                $this->view->assign('is_like',1);
            }else{
                $this->view->assign('is_like',0);
            }
        }

        $this->view->assign('title', '文章详情');
        return $this->fetch();
    }


    /**
     * 处理文章收藏请求
     * @return array
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function fav(){
        if(Request::isAjax()){
            $res = Request::param(); // 登录用户id 文章id
            unset($res['time']);

            $fav = Db::table('zh_user_fav')
                ->where('user_id','=',$res['sessionId'])
                ->where('art_id','=',$res['artId'])
                ->find();

            if(!$fav){ // 当前没有记录 则新增记录
                $info = Db::table('zh_user_fav')->insert([
                    'user_id'   => $res['sessionId'],
                    'art_id'    => $res['artId'],
                    'status'    => 1,
                ]);

                // 查询更新后的收藏量
                $countFav = Db::table('zh_user_fav')
                    ->where('art_id','=',$res['artId'])
                    ->where('status','=',1)
                    ->count();

                if($info){
                    return ['status'=>1,'message'=>'收藏 | '.$countFav];
                }
            }elseif ($fav['status'] === 0){   // 记录存在但未收藏，改为收藏
                $info = Db::table('zh_user_fav')->where([
                    'user_id'   => $res['sessionId'],
                    'art_id'    => $res['artId'],
                ])->update([
                    'status'    => 1,
                ]);

                // 查询更新后的收藏量
                $countFav = Db::table('zh_user_fav')
                    ->where('art_id','=',$res['artId'])
                    ->where('status','=',1)
                    ->count();

                if($info){
                    return ['status'=>1,'message'=>'收藏 | '.$countFav];
                }
            }else{ // 记录存在，已经收藏，改为未收藏
                $info = Db::table('zh_user_fav')->where([
                    'user_id'   => $res['sessionId'],
                    'art_id'    => $res['artId'],
                ])->update([
                    'status'    => 0,
                ]);

                // 查询更新后的收藏量
                $countFav = Db::table('zh_user_fav')
                    ->where('art_id','=',$res['artId'])
                    ->where('status','=',1)
                    ->count();

                if($info){
                    return ['status'=>0,'message'=>'收藏 | '.$countFav];
                }
            }
        }else{
            return ['status'=>-1,'message'=>'非法操作，请重试'];
        }
    }


    /**
     * 处理文章点赞功能
     * @return array
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function like(){
        if(Request::isAjax()){
            $res = Request::param();
            unset($res['time']);

            // 先去查询该记录是否存在
            $like = Db::table('zh_user_like')->where($res)->find();

            if(!$like){   //该点赞记录不存在 添加点赞的记录
                $res['status'] = 1;
                $info = Db::table('zh_user_like')->insert($res);

                $countLike = Db::table('zh_user_like')->where([
                    'art_id'    => $res['art_id'],
                    'status'    => 1,
                ])->count();

                if($info){
                    return ['status'=>1,'message'=>'赞 | '.$countLike];
                }
            }elseif ($like['status'] === 0){  // 点赞记录存在，但是未赞
                $info = Db::table('zh_user_like')->where($res)->update(['status'=>1]);

                $countLike = Db::table('zh_user_like')->where([
                    'art_id'    => $res['art_id'],
                    'status'    => 1,
                ])->count();

                if($info){
                    return ['status'=>1,'message'=>'赞 | '.$countLike];
                }
            }elseif ($like['status'] === 1){
                $info = Db::table('zh_user_like')->where($res)->update(['status'=>0]);

                $countLike = Db::table('zh_user_like')->where([
                    'art_id'    => $res['art_id'],
                    'status'    => 1,
                ])->count();

                if($info){
                    return ['status'=>0,'message'=>'赞 | '.$countLike];
                }
            }
        }else{
            return ['status'=>-1,'message'=>'非法操作，请重试'];
        }
    }


    /**
     * 修改访问量
     * @return bool
     * @throws \think\Exception
     */
    public function pv(){
        $artId = Request::param('art_id');  // art_id

        $res = ArticleModel::where('id','=',$artId)->setInc('pv');
        return true;
    }

    /**
     * 获取登陆用户添加的文章
     */
    public function myArt(){
        $this->isLogin();

        $userId = Request::param('id');

        $res = Db::table('zh_article')
            ->where('user_id','=',$userId)
            ->order('create_time','desc')
            ->paginate(3);
        $artList = $res->toArray()['data'];


        // 查询文章的收藏和点赞量
        foreach ($artList as $key => &$value){
            $criteria = [];
            $criteria['art_id'] = $value['id'];

            $criteria['status'] = 1;
            $countFav = Db::table('zh_user_fav')->where($criteria)->count();
            $value['countFav'] = $countFav;

            $countLike = Db::table('zh_user_like')->where($criteria)->count();
            $value['countLike'] = $countLike;
        }
//        halt($artList);

        $this->assign('artList',$artList);
        $this->assign('res',$res);
        return $this->view->fetch('myArt');
//        return ['status'=>1,'message'=>'成功','content'=>$content];
    }



    public function myFav(){
        $this->isLogin();

        $userId = Request::param('id');
        $sql = "select art.*  from zh_article art , zh_user_fav fav where art.id = fav.art_id and fav.status = 1 and fav.user_id = '$userId' ";
        $artList = Db::query($sql);


        // 查询文章的收藏和点赞量
        foreach ($artList as $key => &$value){
            $criteria = [];
            $criteria['art_id'] = $value['id'];

            $criteria['status'] = 1;
            $countFav = Db::table('zh_user_fav')->where($criteria)->count();
            $value['countFav'] = $countFav;

            $countLike = Db::table('zh_user_like')->where($criteria)->count();
            $value['countLike'] = $countLike;
        }

        $this->assign('artList',$artList);
        return   $this->view->fetch('myFav');
    }
}