<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件
if(!function_exists('getUserName')){
    function getUserName($id){
        return \app\common\model\User::where('id','=',$id)->value('name');
    }
}

if(!function_exists('getArtContent')){
    function getArtContent($content)
    {   if(strlen($content)<30){
            return strip_tags($content);
        }
        return mb_substr(strip_tags($content),0,30).' ...';
    }
}

if(!function_exists('getCatName')){
    function getCatName($id){
        return \app\common\model\ArticleCat::where('id','=',$id)->value('name');
    }
}

if(!function_exists('getRoleName')){
    function getRoleName($role){
        if($role){
            return '超级管理员';
        }else{
            return '普通管理员';
        }
    }
}

if(!function_exists('getSex')){
    function getSex($sex){
        if($sex){
            return '男';
        }else{
            return '女';
        }
    }
}