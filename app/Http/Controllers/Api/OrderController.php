<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Models\Goods;
use App\Models\User;
use App\Models\Order;
use \Illuminate\Http\Request;
use App\Http\Service\ApiService;
use Illuminate\Support\Facades\DB;
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
     * @return {"name":"result","type":"","required":true,"desc":"订单商品信息","level":2}
     * @return {"name":"id","type":"int","required":true,"desc":"订单id","level":3}
     * @return {"name":"increment_id","type":"string","required":true,"desc":"订单号","level":3}
     * @return {"name":"total_qty_ordered","type":"int","required":true,"desc":"商品总数","level":3}
     * @return {"name":"base_grand_total","type":"string","required":true,"desc":"订单金额","level":3}
     * @return {"name":"order_goods","type":"","required":true,"desc":"商品信息","level":3}
     * @return {"name":"name","type":"string","required":true,"desc":"商品名称","level":4}
     * @return {"name":"base_price","type":"float","required":true,"desc":"商品价格","level":4}
     * @return {"name":"product_attribute_id","type":"int","required":true,"desc":"商品属性id","level":4}
     * @return {"name":"thumbnail","type":"string","required":true,"desc":"商品图片","level":4}
     * @example {"code":0,"errCode":200,"message":"加载成功","data":{"page":1,"page_size":4,"total_page_sizes":11,"result":[{"id":107,"increment_id":"20190510162934101579","total_qty_ordered":4,"base_grand_total":"500.0000","order_goods":[{"name":"秋冬棉衣1","base_price":"200.0000","product_attribute_id":46,"thumbnail":null},{"name":"秋冬棉衣2","base_price":"100.0000","product_attribute_id":48,"thumbnail":null}]}]}}
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
     * @param {"name":"seller_id","type":"int","required":true,"desc":"店铺id","level":1}
     * @param {"name":"customer_id","type":"int","required":true,"desc":"客户id","level":1}
     * @param {"name":"address_id","type":"int","required":true,"desc":"收货地址id","level":1}
     * @param {"name":"product","type":"json","required":true,"desc":"商品信息[{'product_id':22,'product_attribute_id':46,'qty_ordered':1},{'product_id':22,'product_attribute_id':48,'qty_ordered':1}]","level":2}
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
            'seller_id.required'             => 41001,
            'seller_id.numeric'              => 42001,
            'customer_id.required'           => 41010,
            'customer_id.numeric'            => 42006,
            'address_id.required'            => 41004,
            'address_id.numeric'             => 42004,
            'product.required'               => 41018,
        ];
        $validator = Validator::make($request->all(), [
            'seller_id'            => 'bail|required|numeric',
            'customer_id'          => 'bail|required|numeric',
            'address_id'           => 'bail|required|numeric',
            'product'              => 'bail|required',
        ],$messages);

        //获取接收参数
        $data = $request->input();
        if(json_decode($data['product']) == ""){
            return ApiService::error(42013);
        }

        $product = json_decode($data['product'],true);
        if(empty($product)){
            return ApiService::error(41018);
        }
        foreach($product as $k=>$v){
            if(empty($v['product_id'])){
                return ApiService::error(41002);
            }
            if(empty($v['qty_ordered'])){
                return ApiService::error(41019);
            }
            if(!is_numeric($v['product_id'])){
                return ApiService::error(42002);
            }
            if(!is_numeric($v['qty_ordered'])){
                return ApiService::error(42014);
            }
        }

        if ($validator->fails()) {
            return ApiService::error($validator->errors()->first());
        }

        $seller_id = $data['seller_id'];
        $customer_id = $data['customer_id'];
        $address_id = $data['address_id'];
        $channel_info =$this->channel_info;

        //获取订单商品信息
        $goods_sku_info = Goods::getOrderGoods($product);
        //获取收货地址信息
        $address_info = Address::getAddressDetail($address_id);
        //获取客户信息
        $customer_info = User::getUser($customer_id);

        DB::transaction(function () use($customer_info,$address_info,$goods_sku_info,$seller_id,$customer_id,$product,$channel_info) {
            //生成订单号
            $increment_id = createOrderNo();
            //创建订单
            $now_time = date("y-m-d H:i:s");
            //订单信息入库
            $id = Order::addOrder($customer_info,$seller_id,$customer_id,$increment_id,$product,$channel_info,$goods_sku_info,$now_time);
            //订单收货地址信息入库
            Order::addOrderAddress($address_info,$customer_info,$id,$customer_id,$now_time);
            //订单商品信息入库
            Order::addOrderItems($id,$goods_sku_info,$product,$now_time);
        });


        return ApiService::success(0);
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
            'status'              => 'required|in:2,4',
        ],$messages);

        if ($validator->fails()) {
            return ApiService::error($validator->errors()->first());
        }

        //获取接收参数
        $data = $request->input();

        //取消收订单
        $result = Order::setOrderStatus([$data['order_ids']],$data['status']);

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
        $result = Order::deleteOrder([$data['order_ids']]);

        return ApiService::success($result);
    }


    /**
     * @title 订单详情
     * @desc  {"0":"接口地址：/api/order/detail","1":"请求方式：GET","2":"开发者: 邹柯"}
     * @param {"name":"order_id","type":"int","required":true,"desc":"订单id","level":1}
     * @return {"name":"code","type":"int","required":true,"desc":"返回码：0成功,-1失败","level":1}
     * @return {"name":"data","type":"","required":true,"desc":"订单详情信息","level":1}
     * @return {"name":"increment_id","type":"string","required":true,"desc":"订单号","level":2}
     * @return {"name":"order_currency_code","type":"string","required":true,"desc":"货币符号","level":2}
     * @return {"name":"order_create_time","type":"date","required":true,"desc":"订单创建时间","level":2}
     * @return {"name":"base_grand_total","type":"string","required":true,"desc":"实付款(订单金额)","level":2}
     * @return {"name":"base_sub_total","type":"string","required":true,"desc":"商品金额","level":2}
     * @return {"name":"base_shipping_amount","type":"string","required":true,"desc":"运费","level":2}
     * @return {"name":"base_discount_amount","type":"string","required":true,"desc":"优惠金额","level":2}
     * @return {"name":"order_goods","type":"","required":true,"desc":"订单商品信息","level":2}
     * @return {"name":"name","type":"string","required":true,"desc":"商品名称","level":3}
     * @return {"name":"base_price","type":"string","required":true,"desc":"商品单价","level":3}
     * @return {"name":"thumbnail","type":"string","required":true,"desc":"商品图片","level":3}
     * @return {"name":"order_address","type":"","required":true,"desc":"订单地址信息","level":2}
     * @return {"name":"first_name","type":"string","required":true,"desc":"姓","level":3}
     * @return {"name":"last_name","type":"string","required":true,"desc":"名","level":3}
     * @return {"name":"phone","type":"string","required":true,"desc":"电话","level":3}
     * @return {"name":"state","type":"string","required":true,"desc":"省/州","level":3}
     * @return {"name":"city","type":"string","required":true,"desc":"城市","level":3}
     * @return {"name":"address1","type":"string","required":true,"desc":"街道详细地址","level":3}
     * @return {"name":"order_payment","type":"","required":true,"desc":"订单支付方式","level":2}
     * @return {"name":"method_title","type":"string","required":true,"desc":"支付方式","level":3}
     * @return {"name":"pay_time","type":"date","required":true,"desc":"支付时间","level":3}
     * @return {"name":"order_shipment","type":"","required":true,"desc":"配送方式信息","level":2}
     * @return {"name":"carrier_title","type":"string","required":true,"desc":"配送方式","level":3}
     * @return {"name":"track_number","type":"string","required":true,"desc":"快递单号","level":3}
     * @return {"name":"shipped_time","type":"date","required":true,"desc":"发货时间","level":3}
     * @return {"name":"status","type":"int","required":true,"desc":"快递状态:0未发货、1已发货、2确认收货","level":3}
     * @example {"code":0,"errCode":200,"message":"加载成功","data":{"increment_id":"20190510162929579756","order_currency_code":"USDT","order_create_time":"2019-05-10 16:29:29","base_grand_total":"500.0000","base_sub_total":"500.0000","base_shipping_amount":"0.0000","base_discount_amount":"0.0000","order_goods":[{"name":"秋冬棉衣1","base_price":"200.0000","product_attribute_id":46,"thumbnail":null},{"name":"秋冬棉衣2","base_price":"100.0000","product_attribute_id":48,"thumbnail":null}],"order_address":{"first_name":"邹柯","last_name":"","phone":"177****5485","state":"上海市","city":"上海市","address1":"宝山区和家欣苑A区5栋101"},"order_payment":{"method_title":"在线支付","pay_time":"2019-05-10 17:15:10"},"order_shipment":{"carrier_title":"顺丰快递","track_number":"2301938465837","shipped_time":"2019-05-10 18:03:12","status":"1"}}}
     */
    public function orderDetail(Request $request){
        //参数校验
        $messages = [
            'order_id.required'  => 41017,
        ];
        $validator = Validator::make($request->all(), [
            'order_id'           => 'required',
        ],$messages);

        if ($validator->fails()) {
            return ApiService::error($validator->errors()->first());
        }

        //获取接收参数
        $data = $request->input();

        //获取订单信息
        $result = Order::getOrder($data['order_id']);
        //获取订单商品信息
        $result['order_goods'] = Order::getOrderGoods([$data['order_id']]);
        //获取订单地址信息
        $result['order_address'] = Order::getOrderAddress($data['order_id']);
        //获取订单支付方式
        $result['order_payment'] = Order::getOrderPayment($data['order_id']);
        //获取订单快递信息
        $result['order_shipment'] = Order::getOrderShipment($data['order_id']);

        return ApiService::success($result);
    }
}