<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Service\ApiService;
use App\Models\Index;

/**
 * @title 商城首页管理
 * @class Index
 * @auth 邹柯
 * @date 2019/05/11
 */
class IndexController extends Controller
{

    /**
     * @title 轮播图
     * @desc  {"0":"接口地址：/api/sliders/list","1":"请求方式：GET","2":"开发者: 邹柯"}
     * @return {"name":"code","type":"int","required":true,"desc":"返回码：0成功,-1失败","level":1}
     * @return {"name":"data","type":"","required":true,"desc":"","level":1}
     * @return {"name":"title","type":"string","required":true,"desc":"轮播图标题","level":2}
     * @return {"name":"path","type":"string","required":true,"desc":"轮播图地址","level":2}
     * @return {"name":"content","type":"string","required":true,"desc":"轮播图内容","level":2}
     * @example {"code":0,"errCode":200,"message":"加载成功","data":[{"title":"len","path":"slider_images/Default/8PqVLpxYUtfxbJIFe6jUmksJACi7eUDjnf49RL7B.jpeg","content":"<h1 style=\"text-align: center;\"><strong>联想</strong></h1>"}]}
     */
    public function slidersList(){
        //获取轮播图信息
        $result = Index::getSliders($this->channel_info);

        return ApiService::success($result);
    }
}