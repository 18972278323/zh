<?php


namespace app\index\controller;


use app\common\controller\Base;
use think\facade\Request;
use app\common\model\User as UserModel;
use think\facade\Session;

class User extends Base
{
    /**
     * 跳转到用户注册页面
     * @return string
     * @throws \think\Exception
     */
    public function register(){
        $this->view->assign('title','用户注册');
        return $this->view->fetch();
    }


    /**
     * 执行插入用户注册数据
     * @return array
     */
    public function insert(){
//            return ['status'=>1,'message'=>'注册成功'];

        if(Request::isAjax()){
            // 接收数据进行验证
            $data = Request::post();
            $rule = "app\\common\\validate\\UserVal";
            $res = $this->validate($data,$rule);

            if($res !== true){
                return ['status'=>-1,'message'=>$res];
            }else{
                // 执行信息保存
                $res = UserModel::create($data);
                if($res){ // ajax提交,默认返回json数据格式
                    return ['status'=>1,'message'=>'注册成功'];
                }else{
                    return ['status'=>0,'message'=>'注册失败'];
                }
            }
        }else{ // 非法操作,提示返回
            $this->redirect(url('User/register',['message'=>'非法操作,请重试']));
        }
    }


    /**
     * 用户登录
     *
     */
    public function login(){
        if(Request::isAjax()){
            $data = Request::post();
            $rule = [
                'username|用户名' => 'require',
                'password|密码'   => 'require',
            ];
            $res = $this->validate($data,$rule);

            if($res !== true){
                return ['status'=>-1,'message'=>$res];  //验证失败
            }else{
                $user = UserModel::get(function ($query) use ($data){
                    $query->where('email','=',$data['username'])->whereOr('mobile','=',$data['username'])->where('password','=',$data['password']);
                });

                // 存储Session
                Session::set('name', $user['name']);
                Session::set('id', $user['id']);
                Session::set('role', $user['is_admin']);

                return ['status'=>1,'message'=>'登录成功']; // 登录成功
            }
        }else if(Request::isGet()){
            $this->logined(); // 判断用户是否已经登录
            return $this->view->fetch('login',['title'=>'欢迎登录']);
        }else{
            return $this->view->fetch('login',['message'=>'非法操作，请重试']);
        }

    }

    /**
     * 用户登出
     */
    public function logout(){
        Session::delete('name');
        Session::delete('id');
        Session::delete('is_admin');

        return $this->redirect(url('index/Index/index'));
    }
}