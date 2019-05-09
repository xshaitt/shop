<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use \Illuminate\Http\Request;
use App\Http\Service\ApiService;
use App\Models\Order;
use Illuminate\Support\Facades\Validator;

/**
 * @title 订单管理
 * @class Order
 * @auth 邹柯
 * @date 2019/05/09
 */
class OrderController extends Controller
{

    /**
     * @title 我的订单
     * @desc  {"0":"接口地址：/api/order/list","1":"请求方式：GET","2":"开发者: 邹柯"}
     * @param {"name":"seller_id","type":"int","required":true,"desc":"店铺id","level":1}
     * @param {"name":"customer_id","type":"int","required":true,"desc":"客户id","level":1}
     * @param {"name":"status","type":"int","required":false,"desc":"订单状态：0全部、1未付款、2已取消、3未发货、4已收货,不传默认0","level":1}
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
     * @example
     */
    public function orderList(Request $request){
        //参数校验
        $messages = [
            'seller_id.required'  => 41001,
            'seller_id.numeric'   => 42001,
            'customer_id.required'=> 41010,
            'customer_id.numeric' => 42006,
            'page.numeric'        => 40001,
            'page_size.numeric'   => 40002,
            'status.in'           => 42011,
        ];
        $validator = Validator::make($request->all(), [
            'seller_id'   => 'bail|required|numeric',
            'customer_id' => 'bail|required|numeric',
            'status'      => 'bail|nullable|in:0,1,2,3,4',
            'page'        => 'bail|nullable|numeric',
            'page_size'   => 'bail|nullable|numeric',
        ],$messages);

        if ($validator->fails()) {
            return ApiService::error($validator->errors()->first());
        }

        //获取接收参数
        $data = $request->input();
        $status = empty($data['status']) ? 0: $data['status'];
        $page = empty($data['page']) ? 1: $data['page'];
        $page_size = empty($data['page_size']) ? 4: $data['page_size'];

        //获取订单列表
        $result = Order::getOrderList($data['seller_id'],$data['customer_id'],$status,$page,$page_size);

        return ApiService::success($result);
    }

    /**
     * @title 创建订单
     * @desc  {"0":"接口地址：/api/order/create","1":"请求方式：POST","2":"开发者: 邹柯"}
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
    public function createOrder(Request $request){
        //参数校验
        $messages = [
            'product_id.required'            => 41002,
            'product_id.numeric'             => 42002,
            'product_attribute_id.required'  => 41003,
            'product_attribute_id.numeric'   => 42003,
        ];
        $validator = Validator::make($request->all(), [
            'product_id'           => 'required|numeric',
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
        $detail = Goods::getGoodsDetail([$product_id],[$product_attribute_id],'zh-cn');
        unset($detail[0]['id']);
        unset($detail[0]['status']);
        unset($detail[0]['parent_id']);
        unset($detail[0]['name']);
        unset($detail[0]['price']);
        $result = $detail[0];
        //获取商品属性
        $result['attributes'] = Goods::getGoodsAttributesByProductId($product_id,$product_attribute_id,'zh-cn');

        return ApiService::success($result);
    }


    /**
     * @title 取消订单或确认收货
     * @desc  {"0":"接口地址：/api/order/setStatus","1":"请求方式：GET","2":"开发者: 邹柯"}
     * @param {"name":"order_ids","type":"string","required":true,"desc":"订单id列表,多个订单id之间用,号分隔开","level":1}
     * @param {"name":"status","type":"int","required":true,"desc":"订单状态:2-取消订单、4-确认收货","level":1}
     * @return {"name":"code","type":"int","required":true,"desc":"返回码：0成功,-1失败","level":1}
     * @return {"name":"data","type":"int","required":true,"desc":"取消订单或确认收货成功的记录数","level":1}
     * @example {"code":0,"errCode":200,"message":"加载成功","data":1}
     */
    public function setOrderStatus(Request $request){
        //参数校验
        $messages = [
            'order_ids.required'  => 41015,
            'status.required'     => 41016,
            'status.in'           => 42012,
        ];
        $validator = Validator::make($request->all(), [
            'order_ids'           => 'required',
            'status'              => 'required|in:0,1',
        ],$messages);

        if ($validator->fails()) {
            return ApiService::error($validator->errors()->first());
        }

        //获取接收参数
        $data = $request->input();

        //取消收订单
        $result = Order::setOrderStatus($data['order_ids'],$data['status']);

        return ApiService::success($result);
    }


    /**
     * @title 删除订单
     * @desc  {"0":"接口地址：/api/order/delete","1":"请求方式：GET","2":"开发者: 邹柯"}
     * @param {"name":"order_ids","type":"string","required":true,"desc":"订单id列表,多个订单id之间用,号分隔开","level":1}
     * @return {"name":"code","type":"int","required":true,"desc":"返回码：0成功,-1失败","level":1}
     * @return {"name":"data","type":"int","required":true,"desc":"删除成功的记录数","level":1}
     * @example {"code":0,"errCode":200,"message":"加载成功","data":1}
     */
    public function deleteOrder(Request $request){
        //参数校验
        $messages = [
            'order_ids.required'  => 41015,
        ];
        $validator = Validator::make($request->all(), [
            'order_ids'           => 'required',
        ],$messages);

        if ($validator->fails()) {
            return ApiService::error($validator->errors()->first());
        }

        //获取接收参数
        $data = $request->input();

        //取消收订单
        $result = Order::deleteOrder($data['order_ids']);

        return ApiService::success($result);
    }
}