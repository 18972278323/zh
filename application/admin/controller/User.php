<?php


namespace app\admin\controller;

use app\admin\controller\Base;
use think\Db;
use think\facade\Config;
use think\facade\Request;
use app\common\model\UserAdmin as UserAdminModel;
use think\facade\Session;
use app\common\model\User as UserModel;


class User extends Base
{
    /**
     * 管理员登录
     * @return array|string
     * @throws \think\Exception
     */
    public function login(){
        if(Request::isAjax()){ // 处理提交的信息
            $user = Request::param();
            $username = $user['username'];
            $password = $user['password'];

            $sql = "SELECT * FROM `zh_user_admin` WHERE ( `email` = '$username' OR
                    `mobile` = '$username') AND `password` = '$password'  AND `status` = 1  LIMIT 1;";
            $userExist = Db::query($sql);

            if(($userExist)){
                $userExist = $userExist[0];
                Session::set('admin_id', $userExist['id']);
                Session::set('admin_name', $userExist['name']);
                Session::set('admin_role' ,$userExist['is_admin']);

                return ['status'=>1,'message'=>'登录成功'];
            }else{
                return ['status'=>0,'message'=>'登录失败，请检查信息或联系管理员'];
            }

        }elseif (Request::isGet()){ // 页面跳转
            return $this->view->fetch('login',['title'=>'后台登录']);
        }else{ //
            return ['status'=>-1,'message'=>'非法操作，请重试'];
        }
    }


    /**
     * 登出操作
     */
    public function logout(){
        Session::delete('admin_id');
        Session::delete('admin_name');
        Session::delete('admin_role');

        return $this->redirect(url('admin/Index/index'));

    }


    /**
     * 展示会员列表
     */
    public function userList()
    {
        if(Request::isGet()){
            // 判断是否登录
            $this->isLogin();

            // 查询数据
            $userList = UserModel::all();

            $this->view->assign('userList',$userList);
            return $this->view->fetch('list',['title'=>'会员列表','content'=>'会员列表','desc'=>'会员列表']);
        }
    }

    /**
     * 用户详情
     */
    public function detail(){
        $id = Request::param('id');
        $this->view->assign('user',UserModel::get($id));
        return $this->view->fetch('detail');
    }

    /**
     * 修改用户状态
     */
    public function changeStatus(){
        $criteria = Request::param();
        $res = UserModel::update($criteria);

        if($res){
            return ['status'=>1,'message'=>'操作成功'];
        }else{
            return ['status'=>0,'message'=>'操作失败'];
        }
    }
}