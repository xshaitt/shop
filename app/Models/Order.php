<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Class Order
 *
 * @author 邹柯
 * @package App\Models
 */
class Order extends Model
{
    /**
     * 我的订单列表
     *
     * @author 邹柯
     * @param $seller_id int 是 店铺id
     * @param $customer_id int 是 客户id
     * @param $status int 是 订单状态：0全部、1未付款、2已取消、3未发货、4已收货
     * @param $page int 是 页码
     * @param $page_size int 是 每页显示条数
     * @return mixed
     */
    public static function getOrderList($seller_id,$customer_id,$status,$page,$page_size){
        $offset = ($page - 1) * $page_size;
        //总记录数
        if($status == 0){
            $status = [1,2,3,4];
        }else{
            $status = [$status];
        }
        //订单记录数
        $count = Db::table('orders')
            ->whereIn('status',$status)
            ->where([
            ['is_deleted',0],
            ['seller_id',$seller_id],
            ['customer_id',$customer_id]
        ])->count();
        //总页数
        $total_page_sizes = ceil($count/$page_size);

        if($count > 0){
            $result = Db::table('orders')->addSelect(['increment_id','total_qty_ordered','grand_total'])
                ->whereIn('status',$status)
                ->where([
                    ['is_deleted',0],
                    ['seller_id',$seller_id],
                    ['customer_id',$customer_id]
                ])->offset($offset)->limit($page_size)->orderBy('id','DESC')->get();

            $result = object_to_array($result);
            $order_ids = array_column($result,'increment_id');
            //根据订单id获取商品id列表
            $product_ids = self::getGoodsIdsByOrderIds($order_ids);
            //根据商品id获取商品信息
            $goods_detail = Goods::getGoodsAttributes($product_ids);
            print_r($goods_detail);die;
        }else{
            $result = [];
        }



        return ['page'=>$page,'page_size'=>$page_size,'total_page_sizes'=>$total_page_sizes,'result'=>$result];
    }

    /**
     * 根据订单id获取商品id列表
     *
     * @author 邹柯
     * @param $order_ids string 是 订单id列表
     * @return array|\Illuminate\Support\Collection
     */
    private static function getGoodsIdsByOrderIds($order_ids){
        $result = Db::table('order_item')->addSelect(['order_id',DB::raw('group_concat(product_id) as product_ids')])
            ->whereIn('order_id',$order_ids)
            ->groupBy('order_id')
            ->get();
        if(!empty($result)){
            $result = object_to_array($result);
        }else{
            $result = [];
        }

        return $result;
    }

    /**
     * 订单入库
     *
     * @author 邹柯
     * @param $customer_info array 是 客户信息
     * @param $seller_id int 是 店铺id
     * @param $customer_id int 是 客户id
     * @param $order_id string 是 订单id
     * @param $product array 是 商品信息
     * @param $now_time datetime 是 入库时间
     * @return bool
     */
    public static function addOrder($customer_info,$seller_id,$customer_id,$increment_id,$product,$channel_info,$now_time){
        $total_item_count = count(array_unique(array_column($product,'product_id')));
        $total_qty_ordered = array_sum(array_column($product,'qty_ordered'));

        return Db::table('orders')->insertGetId([
            'increment_id'=>$increment_id,
            'status'=>1,
            'channel_id'=>$channel_info['id'],
            'channel_name'=>$channel_info['channel_name'],
            'channel_type'=>$channel_info['channel_code'],
            'is_guest'=>0,
            'customer_email'=>$customer_info['email'],
            'customer_first_name'=>$customer_info['first_name'],
            'customer_last_name'=>$customer_info['last_name'],
            'shipping_method'=>null,
            'shipping_title'=>null,
            'shipping_description'=>null,
            'coupon_code'=>null,
            'is_gift'=>0,
            'total_item_count'=>$total_item_count,
            'total_qty_ordered'=>$total_qty_ordered,
            'base_currency_code'=>$channel_info['currency_code'],
            'customer_id'=>$customer_id,
            'customer_type'=>$customer_info['customer_type'],
            'created_at'=>$now_time,
            'updated_at'=>$now_time,
            'seller_id'=>$seller_id
        ]);
    }

    /**
     * 订单商品入库
     *
     * @param $order_id int 是 订单id
     * @param $goods_sku_info array 是 商品信息
     * @param $product array
     * @param $now_time datetime 是 创建时间
     * @return bool
     */
    public static function addOrderItems($order_id,$goods_sku_info,$product,$now_time){
        foreach($goods_sku_info as $k=>$v){
            $g[$v['id']]['name'] = $v['name'];
            $g[$v['id']]['price'] = $v['price'];
            //$g[$v['id']]['parent_id'] = $v['parent_id'];
        }
        foreach($product as $k=>$v){
            $product_attribute_name = $g[$v['product_attribute_id']]['name'];
            $product_attribute_price = $g[$v['product_attribute_id']]['price'];
            //$parent_id = $g[$v['product_attribute_id']]['parent_id'];
            $total = $product_attribute_price * $v['qty_ordered'];
            $order_items[] = [
                'name'=>$product_attribute_name,
                'qty_ordered'=>$v['qty_ordered'],
                'price'=>$product_attribute_price,
                'total'=>$total,
                'product_id'=>$v['product_attribute_id'],
                'order_id'=>$order_id,
                'parent_id'=>null,
                'created_at'=>$now_time,
                'updated_at'=>$now_time,
            ];
        }

        return Db::table('order_items')->insert($order_items);
    }


    /**
     * 订单收货地址信息入库
     *
     * @author 邹柯
     * @param $address_info array 是 收货地址信息
     * @param $order_id string 是 订单id
     * @param $customer_id int 是 客户id
     * @param $now_time datetime 是 入库时间
     * @return bool
     */
    public static function addOrderAddress($address_info,$customer_info,$order_id,$customer_id,$now_time){
        //订单收货地址入库
        return Db::table('order_address')->insert([
            'first_name'=>$customer_info['first_name'],
            'last_name'=>$customer_info['last_name'],
            'email'=>$customer_info['email'],
            'address1'=>$address_info['address1'],
            'address2'=>$address_info['address2'],
            'country'=>$address_info['country'],
            'state'=>$address_info['state'],
            'city'=>$address_info['city'],
            'postcode'=>$address_info['postcode'],
            'phone'=>$address_info['phone'],
            'address_type'=>"",
            'order_id'=>$order_id,
            'customer_id'=>$customer_id,
            'created_at'=>$now_time,
            'updated_at'=>$now_time
        ]);
    }


    /**
     * 设置订单状态
     *
     * @author 邹柯
     * @param $order_ids string 是 订单id列表,多个订单id用,分隔开
     * @param $status int 是 订单状态:2-取消订单、4-确认收货
     * @return int
     */
    public static function setOrderStatus($order_ids,$status){
        return DB::table('orders')->whereIn('increment_id',$order_ids)->update(['status'=>$status]);
    }

    /**
     * 删除订单
     *
     * @author 邹柯
     * @param $order_ids string 是 订单id列表,多个订单id用,分隔开
     * @return int
     */
    public static function deleteOrder($order_ids){
        return DB::table('orders')->whereIn('increment_id',$order_ids)->update(['is_deleted'=>1]);
    }

    /**
     * 订单详情
     *
     * @author 邹柯
     * @param $order_id int 是 订单id
     * @return array|null
     */
    public static function getOrderDeatil($order_id){
        return null;
    }
}
