<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use \Illuminate\Http\Request;
use App\Http\Service\ApiService;
use App\Models\Goods;
use Illuminate\Support\Facades\Validator;

/**
 * @title 商品管理
 * @class Goods
 * @auth 邹柯
 * @date 2019/05/06~2019/05/07
 */
class GoodsController extends Controller
{

    /**
     * @title 商品列表
     * @desc  {"0":"接口地址：/api/goods/list","1":"请求方式：GET","2":"开发者: 邹柯"}
     * @param {"name":"seller_id","type":"int","required":true,"desc":"店铺id","level":1}
     * @param {"name":"page","type":"int","required":false,"desc":"页码,不传默认1","level":1}
     * @param {"name":"page_size","type":"int","required":false,"desc":"每页显示条数，不传默认4","level":1}
     * @return {"name":"code","type":"int","required":true,"desc":"返回码：0成功,-1失败","level":1}
     * @return {"name":"data","type":"","required":true,"desc":"","level":1}
     * @return {"name":"page","type":"int","required":true,"desc":"页码","level":2}
     * @return {"name":"page_size","type":"int","required":true,"desc":"每页显示条数","level":2}
     * @return {"name":"total_page_sizes","type":"int","required":true,"desc":"总页数","level":2}
     * @return {"name":"result","type":"dict","required":true,"desc":"商品及分类信息","level":2}
     * @return {"name":"category_name","type":"string","required":true,"desc":"分类名称","level":3}
     * @return {"name":"child_info","type":"dict","required":true,"desc":"商品信息","level":3}
     * @return {"name":"product_id","type":"int","required":true,"desc":"商品id","level":4}
     * @return {"name":"name","type":"string","required":true,"desc":"商品名称","level":4}
     * @return {"name":"quantity","type":"int","required":true,"desc":"商品数量","level":4}
     * @return {"name":"price","type":"string","required":true,"desc":"商品价格","level":4}
     * @return {"name":"image_paths","type":"string","required":false,"desc":"商品图片","level":4}
     *
     * @example {"code":0,"errCode":200,"message":"加载成功","data":{"page":"1","page_size":"4","total_page_sizes":2,"result":[{"category_name":"男装","child_info":[{"product_id":22,"name":"秋冬棉衣","quantity":"0","price":"0","image_paths":"product/22/AgeQ5CDyidcL5P5LDqyD1V5nQ5Zms9y67vP7Hk2t.jpeg,product/22/e39SQ98DKHH0YU1WHTqRuaSWaZH5su871C0hKwWj.jpeg,product/22/YxoVj0YghLu1OrFWiS8aPRwCyqDSan016nuQw6eb.jpeg"}]},{"category_name":333,"child_info":[{"product_id":43,"name":"dsdsd","quantity":"200","price":"200","image_paths":null}]}]}}]}]}
     */
    public function goodsList(Request $request){
        //参数校验
        $messages = [
            'seller_id.required'  => 41001,
            'seller_id.numeric'   => 42001,
            'page.numeric'        => 40001,
            'page_size.numeric'   => 40002,
        ];
        $validator = Validator::make($request->all(), [
            'seller_id' => 'bail|required|numeric',
            'page'      => 'bail|nullable|numeric',
            'page_size' => 'bail|nullable|numeric',
        ],$messages);

        if ($validator->fails()) {
            return ApiService::error($validator->errors()->first());
        }

        //获取接收参数
        $data = $request->input();
        $seller_id = $data['seller_id'];
        $page = empty($data['page']) ? 1: $data['page'];
        $page_size = empty($data['page_size']) ? 4: $data['page_size'];

        //获取商品列表
        $result = Goods::getGoodsByPage($seller_id,$page,$page_size);
        if(!empty($result['result'])){
            $product_ids = array_column($result['result'],'product_id');
            //根据商品id获取商品图片
            $product_image_info = Goods::getGoodsImageByProductIds($product_ids);
            if(!empty($product_image_info)){
                foreach($product_image_info as $k=>$v){
                    $product_image[$v['product_id']] = $v['image_paths'];
                }
            }

        }


        //根据商品id获取商品的分类
        $category_info = Goods::getGoodsCategoryByProductIds($product_ids);
        if(!empty($category_info)) {
            foreach ($category_info as $k => $v) {
                $cate[$v['product_id']] = $v['name'];
            }
        }


        //组装数据
        if(!empty($result['result'])){
            foreach($result['result'] as $k=>$v){
                $result['result'][$k]['category_name'] = isset($cate[$v['product_id']]) ? $cate[$v['product_id']] : null;
                $result['result'][$k]['image_paths'] = isset($product_image[$v['product_id']]) ? $product_image[$v['product_id']] : null;
            }
            //按分类进行分组
            $res = array_to_group('category_name',$result['result']);
        }


        return ApiService::success(['page'=>$result['page'],'page_size'=>$result['page_size'],'total_page_sizes'=>$result['total_page_sizes'],'result'=>$res]);
    }

    /**
     * @title 商品详情
     * @desc  {"0":"接口地址：/api/goods/detail","1":"请求方式：GET","2":"开发者: 邹柯"}
     * @param {"name":"product_id","type":"int","required":true,"desc":"商品id","level":1}
     * @param {"name":"product_attribute_id","type":"int","required":true,"desc":"商品属性id","level":1}
     * @return {"name":"code","type":"int","required":true,"desc":"返回码：0成功,-1失败","level":1}
     * @return {"name":"data","type":"","required":true,"desc":"","level":1}
     * @return {"name":"product_id","type":"int","required":true,"desc":"商品id","level":2}
     * @return {"name":"description","type":"string","required":false,"desc":"商品描述","level":2}
     * @return {"name":"new","type":"int","required":true,"desc":"是否新品:1是、0否","level":2}
     * @return {"name":"featured","type":"int","required":true,"desc":"是否特色商品:1是、0否","level":2}
     * @return {"name":"status","type":"int","required":true,"desc":"是否启用:1是、0否","level":2}
     * @return {"name":"visible_individually","type":"int","required":true,"desc":"是否可见:1是、0否","level":2}
     * @return {"name":"image_paths","type":"string","required":false,"desc":"商品轮播图","level":2}
     * @return {"name":"attributes","type":"dict","required":true,"desc":"商品属性","level":2}
     * @return {"name":"product_attribute_id","type":"int","required":true,"desc":"商品属性id","level":3}
     * @return {"name":"status","type":"int","required":true,"desc":"是否启用:1是、0否","level":3}
     * @return {"name":"goods_name","type":"string","required":true,"desc":"商品sku名称","level":3}
     * @return {"name":"attributes","type":"string","required":true,"desc":"商品sku属性","level":3}
     * @return {"name":"price","type":"string","required":true,"desc":"商品sku价格","level":3}
     * @return {"name":"is_selected","type":"int","required":true,"desc":"商品sku是否选中:1是、0否","level":3}
     * @example {"code":0,"errCode":200,"message":"加载成功","data":{"product_id":22,"description":"<p>wwewewe</p>","new":1,"featured":0,"status":1,"visible_individually":0,"image_paths":"product/22/AgeQ5CDyidcL5P5LDqyD1V5nQ5Zms9y67vP7Hk2t.jpeg,product/22/e39SQ98DKHH0YU1WHTqRuaSWaZH5su871C0hKwWj.jpeg,product/22/YxoVj0YghLu1OrFWiS8aPRwCyqDSan016nuQw6eb.jpeg","attributes":[{"product_attribute_id":46,"status":1,"goods_name":"秋冬棉衣1","attributes":"颜色:Red 尺码:","price":"200.0000","is_selected":1},{"product_attribute_id":48,"status":1,"goods_name":"秋冬棉衣2","attributes":"颜色:Green 尺码:S","price":"100.0000","is_selected":0}]}}
     */
    public function goodsDetail(Request $request){
        //参数校验
        $messages = [
            'product_id.required'  => 41002,
            'product_id.numeric'   => 42002,
            'product_attribute_id.required'  => 41003,
            'product_attribute_id.numeric'   => 42003,
        ];
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|numeric',
            'product_attribute_id' => 'required|numeric',
        ],$messages);

        if ($validator->fails()) {
            return ApiService::error($validator->errors()->first());
        }

        //获取接收参数
        $data = $request->input();
        $product_id = $data['product_id'];
        $product_attribute_id = $data['product_attribute_id'];

        //获取商品详情
        $detail = Goods::getGoodsDetail($product_id,'zh-cn');

        //获取商品属性
        $detail['attributes'] = Goods::getGoodsSkuAttribute($product_id,$product_attribute_id);

        return ApiService::success($detail);
    }
}