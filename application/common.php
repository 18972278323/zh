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
function getUserName($id){
    return \app\common\model\User::where('id','=',$id)->value('name');
}

function getArtContent($content)
{   if(strlen($content)<30){
        return $content;
    }
    return mb_substr(strip_tags($content),0,30).' ...';
}

function getCatName($id){
    return \app\common\model\ArticleCat::where('id','=',$id)->value('name');
}