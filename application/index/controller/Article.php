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
use app\common\model\Comment as CommentsModel;
use think\Db;
use think\facade\Request;
use think\facade\Session;
use think\facade\Cookie;

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

            $cateList = ArticleCat::all()->order('sort','asc');
            if(count($cateList)>0 ){
                $this->assign('cateList',$cateList);
            }else{
                return $this->redirect(url('ArticleCate/add'));
            }

            $this->view->assign('title','发布文章');
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

            $artList = Db::table('zh_article')
                ->where($maps)
                ->order('create_time','desc')
                ->paginate(3,false,['query'=>$_GET]);  //为分页查询添加条件

            // 如果指定分类条件
            if($cateId){
                $cateName = ArticleCat::where('id','=',$cateId)->value('name');
                $this->view->assign('cateName', $cateName);
            }else{  // 未指定分类条件
                $this->view->assign('cateName', '全部文章');
            }

            $this->view->assign('artList', $artList);
            $this->view->assign('title', '首页');
            $this->view->assign('empty','当前暂没有数据');
            return $this->view->fetch('index/index');

        }
    }

    /**
     * 获取文章详情
     * @return mixed
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

        // 查询文章评论总数和评论列表
        $commentsCount = CommentsModel::where('status', '=', '1')->count();
        $commentsList = CommentsModel::all(function ($query) use ($artId){
           $query->where('status','=','1')
           ->where('art_id','=',$artId);
        });
        $this->view->assign('comments', $commentsList);
        $this->view->assign('comCount', $commentsCount);

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
        }else{
            // 如果用户未登陆，将当前页面详情链接存储Cookie，便于用户点赞或收藏后跳转到该页面
            $host = Request::domain();
            $baseUrl = Request::baseUrl();
            Cookie::set('url',$host.$baseUrl);
        }

        $this->view->assign('title', '文章详情');
        $page = Request::param('page');
        if($page){  // 如果指定了要返回的页面
            return $this->view->fetch($page);
        }
        return $this->view->fetch();
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
     */
    public function like(){
        if(Request::isAjax()){
            $this->isLogin();

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

        $userId = Session::get('id');

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

        $this->assign('artList',$artList);
        $this->assign('res',$res);
        return $this->view->fetch('myArt',['title'=>'我的文章']);
    }


    /**
     * 我的收藏列表
     * @return string
     */
    public function myFav(){
        $this->isLogin();

        $userId = Session::get('id');
        $sql = "select art.*  from zh_article art , zh_user_fav fav where art.id = fav.art_id and fav.status = 1 and fav.user_id = '$userId' order by art.create_time desc ";
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
        return   $this->view->fetch('myFav',['title'=>'我的收藏']);
    }

    /**
     * 去文章编辑界面
     * @return string
     */
    public function editArt()
    {
        if (Request::isGet()) {
            $this->isLogin();

            $criteria = [];
            $artId = Request::param('id');
            $userId = Session::get('id');
            $criteria['id'] = $artId;
            $criteria['user_id'] = $userId;

            $art = ArticleModel::get(function ($query) use ($criteria) {
                $query->where($criteria);
            });

            $this->view->assign('art', $art);
            return $this->view->fetch('detail_edit', ['title' => '编辑文章']);
        } elseif (Request::isAjax()) {
            // 获取基本信息
            $data = Request::param();
            $rule = [
                'title|文章标题' => 'require',
                'cate_id|文章分类' => 'require',
                'content|文章内容' => 'require',
            ];

            $valRes = $this->validate($data, $rule);
            if ($valRes !== true) {
                return ['status' => 0, 'message' => $valRes];

            }

            // 处理文件上传
            $file = Request::file('');
            if ($file) {  // 说明有文件上传
                $img = $file['title_img'];
                $uploadRes = $img->validate([
                    'ext' => ['jpg', 'jpeg', 'gif', 'png'],
                    'size' => 5000000,
                ])->move('uploads');

                if ($uploadRes) {
                    $data['title_img'] = $uploadRes->getSaveName();
                } else {
                    $messege = $img->getError();
                    return ['status' => 0, 'message' => $messege];
                }
            }

            $res = ArticleModel::update($data);
            if ($res) {
                return ['status' => 1, 'message' => '修改成功'];
            } else {
                return ['status' => 0, 'message' => '文件上传成功，但是文章保存失败'];
            }
        }

    }


    // 被驳回的文章提交审核
    public function subCheck(){
        // 获取基本信息
        $data = Request::param();
        $rule = [
            'title|文章标题'         => 'require',
            'cate_id|文章分类'       => 'require',
            'content|文章内容'       => 'require',
        ];

        $valRes = $this->validate($data,$rule);
        if($valRes !== true ){
            return ['status'=>0,'message'=>$valRes];
        }

        // 处理文件上传
        $file = Request::file('');
        if($file){  // 说明有文件上传
            $img = $file['title_img'];
            $uploadRes = $img->validate([
                'ext' => ['jpg','jpeg','gif','png'],
                'size'=> 5000000,
            ])->move('uploads');

            if($uploadRes){
                $data['title_img'] = $uploadRes->getSaveName();
            }else{
                $messege = $img->getError();
                return ['status'=>0,'message'=>$messege];
            }
        }
        $data['status'] = -2;  //提交审核的状态

        $res = ArticleModel::update($data);
        if($res){
            return ['status'=>1,'message'=>'提交审核成功'];
        }else{
            return ['status'=>0,'message'=>'文件上传成功，但是文章保存失败'];
        }
    }


    /**
     * 修改文章状态，1正常 0删除  -1驳回  -2待审核
     * @return array
     */
    public function chgStatus(){
        $this->isLogin();

        $criteria = [];
        $criteria['id'] = Request::param('id');
        $criteria['user_id'] = Session::get('id');
        $criteria['status'] = Request::param('status');

        $res = ArticleModel::update($criteria);
        if($res){
            return ['status'=>1,'message'=>'操作成功'];
        }else{
            return ['status'=>0,'message'=>'非法操作'];
        }

    }

}