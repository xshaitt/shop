<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Goods;
use \Illuminate\Http\Request;
use App\Http\Service\ApiService;
use App\Models\Cart;
use App\Models\Index;
use Illuminate\Support\Facades\Validator;

/**
 * @title 购物车管理
 * @class Index
 * @auth 邹柯
 * @date 2019/05/11
 */
class CartController extends Controller
{

    /**
     * @title 加入购物车
     * @desc  {"0":"接口地址：/api/cart/add","1":"请求方式：POST","2":"开发者: 邹柯"}
     * @param {"name":"seller_id","type":"int","required":true,"desc":"店铺id","level":1}
     * @param {"name":"customer_id","type":"int","required":true,"desc":"客户id","level":1}
     * @param {"name":"product_id","type":"int","required":true,"desc":"商品id","level":1}
     * @param {"name":"product_attribute_id","type":"int","required":true,"desc":"商品属性id","level":1}
     * @return {"name":"code","type":"int","required":true,"desc":"返回码：0成功,-1失败","level":1}
     * @return {"name":"data","type":"int","required":true,"desc":"加入购物车成功的记录数","level":1}
     * @example {"code":0,"errCode":200,"message":"加载成功","data":true}
     */
    public function addCart(Request $request){
        //参数校验
        $messages = [
            'seller_id.required'             => 41001,
            'seller_id.numeric'              => 42001,
            'customer_id.required'           => 41010,
            'customer_id.numeric'            => 42006,
            'product_id.required'            => 41002,
            'product_id.numeric'             => 42002,
            'product_attribute_id.required'  => 41003,
            'product_attribute_id.numeric'   => 42003,
        ];

        $validator = Validator::make($request->all(), [
            'seller_id'            => 'bail|required|numeric',
            'customer_id'          => 'bail|required|numeric',
            'product_id'           => 'required|numeric',
            'product_attribute_id' => 'required|numeric',
        ],$messages);

        if ($validator->fails()) {
            return ApiService::error($validator->errors()->first());
        }

        //获取接收参数
        $data = $request->input();
        //加入购物车
        $result = Cart::addCart($data['seller_id'],$data['customer_id'],$data['product_id'],$data['product_attribute_id']);

        return ApiService::success($result);
    }

    /**
     * @title 增加商品数量
     * @desc  {"0":"接口地址：/api/cart/up","1":"请求方式：GET","2":"开发者: 邹柯"}
     * @param {"name":"product_cart_id","type":"int","required":true,"desc":"购物车id","level":1}
     * @param {"name":"count","type":"int","required":true,"desc":"商品数量","level":1}
     * @return {"name":"code","type":"int","required":true,"desc":"返回码：0成功,-1失败","level":1}
     * @return {"name":"data","type":"int","required":true,"desc":"增加成功的记录数","level":1}
     * @example {"code":0,"errCode":200,"message":"加载成功","data":1}
     */
    public function upCart(Request $request){
        //参数校验
        $messages = [
            'product_cart_id.required'   => 41022,
            'product_cart_id.numeric'    => 42017,
            'count.required'             => 41023,
            'count.numeric'              => 42018,
        ];

        $validator = Validator::make($request->all(), [
            'product_cart_id'  => 'bail|required|numeric',
            'count'            => 'bail|required|numeric',
        ],$messages);

        if ($validator->fails()) {
            return ApiService::error($validator->errors()->first());
        }

        //获取接收参数
        $data = $request->input();
        //获取购物车商品属性id
        $product_attribute_info = Cart::getCartGoodsByCartId($data['product_cart_id']);
        //判断商品库存
        $inventorie = Cart::getCartGoodsInventory($product_attribute_info['product_attribute_id']);
        if($inventorie < $data['count'] + $product_attribute_info['count']){
            return ApiService::error(43000);
        }
        //增加购物车商品数量
        $result = Cart::upCart($data['product_cart_id'],$data['count']);

        return ApiService::success($result);
    }

    /**
     * @title 减少商品数量
     * @desc  {"0":"接口地址：/api/cart/down","1":"请求方式：GET","2":"开发者: 邹柯"}
     * @param {"name":"product_cart_id","type":"int","required":true,"desc":"购物车id","level":1}
     * @param {"name":"count","type":"int","required":true,"desc":"商品数量","level":1}
     * @return {"name":"code","type":"int","required":true,"desc":"返回码：0成功,-1失败","level":1}
     * @return {"name":"data","type":"int","required":true,"desc":"减少成功的记录数","level":1}
     * @example {"code":0,"errCode":200,"message":"加载成功","data":1}
     */
    public function downCart(Request $request){
        //参数校验
        $messages = [
            'product_cart_id.required'   => 41022,
            'product_cart_id.numeric'    => 42017,
            'count.required'             => 41023,
            'count.numeric'              => 42018,
        ];

        $validator = Validator::make($request->all(), [
            'product_cart_id'  => 'bail|required|numeric',
            'count'            => 'bail|required|numeric',
        ],$messages);

        if ($validator->fails()) {
            return ApiService::error($validator->errors()->first());
        }

        //获取接收参数
        $data = $request->input();
        //减少购物车商品数量
        $result = Cart::downCart($data['product_cart_id'],$data['count']);


        return ApiService::success($result);
    }


    /**
     * @title 删除购物车商品
     * @desc  {"0":"接口地址：/api/cart/del","1":"请求方式：GET","2":"开发者: 邹柯"}
     * @param {"name":"product_cart_ids","type":"string","required":true,"desc":"购物车id列表,多个之间用,号分隔","level":1}
     * @return {"name":"code","type":"int","required":true,"desc":"返回码：0成功,-1失败","level":1}
     * @return {"name":"data","type":"int","required":true,"desc":"删除成功的记录数","level":1}
     * @example {"code":0,"errCode":200,"message":"加载成功","data":1}
     */
    public function delCart(Request $request){
        //参数校验
        $messages = [
            'product_cart_ids.required'   => 41024,
        ];

        $validator = Validator::make($request->all(), [
            'product_cart_ids'  => 'bail|required',
        ],$messages);

        if ($validator->fails()) {
            return ApiService::error($validator->errors()->first());
        }

        //获取接收参数
        $data = $request->input();
        //减少购物车商品数量
        $result = Cart::delCart($data['product_cart_ids']);


        return ApiService::success($result);
    }


    /**
     * @title 购物车列表
     * @desc  {"0":"接口地址：/api/cart/list","1":"请求方式：GET","2":"开发者: 邹柯"}
     * @param {"name":"seller_id","type":"int","required":true,"desc":"店铺id","level":1}
     * @param {"name":"customer_id","type":"int","required":true,"desc":"客户id","level":1}
     * @return {"name":"code","type":"int","required":true,"desc":"返回码：0成功,-1失败","level":1}
     * @return {"name":"data","type":"","required":true,"desc":"","level":1}
     * @return {"name":"total_price","type":"float","required":true,"desc":"商品合计","level":2}
     * @return {"name":"product","type":"","required":true,"desc":"商品信息","level":2}
     * @return {"name":"id","type":"int","required":true,"desc":"商品属性id","level":3}
     * @return {"name":"name","type":"string","required":true,"desc":"商品名称","level":3}
     * @return {"name":"price","type":"float","required":true,"desc":"商品价格","level":3}
     * @return {"name":"count","type":"int","required":true,"desc":"商品购买数量","level":3}
     * @return {"name":"status","type":"int","required":true,"desc":"是否上下架:1是、0否","level":3}
     * @return {"name":"thumbnail","type":"string","required":true,"desc":"缩略图地址","level":3}
     * @example {"code":0,"errCode":200,"message":"加载成功","data":{"total_price":1000,"product":[{"id":46,"name":"秋冬棉衣1","price":"200.0000","status":1,"thumbnail":null,"count":4}]}}
     */
    public function cartList(Request $request){
        //参数校验
        $messages = [
            'seller_id.required'             => 41001,
            'seller_id.numeric'              => 42001,
            'customer_id.required'           => 41010,
            'customer_id.numeric'            => 42006,
        ];

        $validator = Validator::make($request->all(), [
            'seller_id'            => 'bail|required|numeric',
            'customer_id'          => 'bail|required|numeric',
        ],$messages);

        if ($validator->fails()) {
            return ApiService::error($validator->errors()->first());
        }
        //获取接收参数
        $data = $request->input();
        //获取购物车商品id
        $product_attribute_info = Cart::getCartProductAttributeIds($data['seller_id'],$data['customer_id']);
        foreach($product_attribute_info as $k=>$v){
            $p[$v['product_attribute_id']] = $v['count'];
        }
        $product_attribute_ids = array_column($product_attribute_info,'product_attribute_id');
        //根据商品属性id获取商品属性信息
        $product_attribute_detail = Goods::getGoodsAttributes($product_attribute_ids);

        $total_price = 0;
        foreach($product_attribute_detail as $k=>$v){
            unset($product_attribute_detail[$k]['channel']);
            unset($product_attribute_detail[$k]['parent_id']);
            $product_attribute_detail[$k]['count'] = $p[$v['id']];

            $total_price +=  $p[$v['id']] * $v['price'];
        }
        return ApiService::success(['total_price'=>$total_price,'product'=>$product_attribute_detail]);
    }
}