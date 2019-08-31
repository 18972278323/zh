<?php


namespace app\admin\controller;

use app\admin\controller\Base;
use think\Db;
use think\facade\Request;
use app\common\model\UserAdmin as UserAdminModel;
use think\facade\Session;


class User extends Base
{
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
}