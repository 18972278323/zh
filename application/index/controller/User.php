<?php


namespace app\index\controller;


use app\common\controller\Base;
use think\facade\Request;
use app\common\model\User as UserModel;

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

        if(Request::isAjax()){
            // 接收数据进行验证
            $data = Request::post();
            dump($data);die;
            $rule = "app\\common\\validate\\UserVal";
            $res = $this->validate($data,$rule);
            $res = true;

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
}