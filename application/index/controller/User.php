<?php


namespace app\index\controller;


use app\common\controller\Base;
use think\facade\Request;
use app\common\model\User as UserModel;
use think\facade\Session;
use think\Db;


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
                // 对密码进行加密
                $data['password'] = md5($data['password']);

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

                $username = $data['username'];
                $password = md5($data['password']);

                $sql = "SELECT * FROM `zh_user` WHERE (`email` = '$username' OR
                    `mobile` = '$username') AND `password` = '$password'  AND `status` = 1  LIMIT 1;";

                $userExist = Db::query($sql);

                // 存储Session
                if($userExist){
                    $data = $userExist[0];
                    Session::set('name', $data['name']);
                    Session::set('id', $data['id']);
                    Session::set('role', $data['is_admin']);

                    return ['status'=>1,'message'=>'登录成功']; // 登录成功
                }else{
                    return ['status'=>0,'message'=>'登录信息有误或还未注册']; // 登录成功

                }

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


    /**
     * 获取用户信息
     */
    public function info(){
        $this->isLogin();

        $id = Session::get('id');
        $user = UserModel::get($id);
        $this->assign('user',$user);
        return $this->view->fetch('info',['title'=>'详细信息']);
    }

    /**
     * 修改密码
     */
    public function editPassword(){
        $this->isLogin();

        if(Request::isGet()){  // 直接跳转页面
            return $this->view->fetch('editPassword');

        }elseif (Request::isAjax()){  // 处理提交的修改数据
            $data = Request::param();
            $rule = [
                'password_input|旧密码'  => 'require',
                'password|新密码'  => 'require|chsAlphaNum|confirm',
            ];
            $res = $this->validate($data,$rule);

            if($res !== true){  //验证失败 -1
                return ['status'=>-1,'message'=>$res];
            }else{ // 验证成功
                $id = Session::get('id');
                $passwordInput = md5($data['password_input']);
                $passwordNew = md5($data['password']);

                // 判断原密码是否正确
                $user = UserModel::get($id);
                $passwordOld = $user['password'];

                if ($passwordOld == $passwordNew){
                    return ['status'=>-1,'message'=>'新密码不可与原密码相同，请重试'];
                }

                if($passwordInput != $passwordOld){  // 密码输入错误
                    return ['status'=>-1,'message'=>'原密码输入错误，请重试'];
                }else{
                    // 修改密码
                    $updateCri = [
                        'id' => $id,
                        'password' => $passwordNew,
                    ];

                    $res = UserModel::update($updateCri);
                    if($res){
                        return ['status'=>1,'message'=>'密码修改成功，点击确定将退出重新登录'];
                    }else{
                        return ['status'=>0,'message'=>'密码修改失败'];
                    }
                }

            }

        }

    }

    /**
     * 更细用户信息
     */
    public function update(){
        $data = Request::param();
        $rule = [
            'name|姓名'   => 'require',
            'sex|性别'    => 'require',
            'mobile|手机号码'      => 'require|mobile',
            'email|邮箱'  => 'require|email',
            'birthday|生日'    => 'require',
        ];

        $res = $this->validate($data,$rule);
        if($res !== true){  //验证失败 -1
            return ['status'=>-1,'message'=>$res];
        }else{ // 验证成功 1
            $res = UserModel::update($data);

            if($res){
                return ['status'=>1,'message'=>'更新成功'];
            }else{
                return ['status'=>0,'message'=>'更新失败'];
            }
        }
    }

}