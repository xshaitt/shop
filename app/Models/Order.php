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
     * 创建订单
     *
     * @author 邹柯
     * @param $customer_id int 是 客户id
     * @param $country string 是 国家
     * @param $state string 是 省/州
     * @param $city string 是 城市
     * @param $address1 string 是 街道地址
     * @param $postcode string 是 邮政编码
     * @param $default_address int 是 是否默认:1是、0否
     * @return boolean
     */
    public static function createAddress($customer_id,$country,$state = "",$city = "",$address1 = "",$postcode = 0,$phone = "",$default_address = null){
        $count = Db::table('customer_addresses')->where([
            ['customer_id','=',$customer_id]
        ])->count();
        $address_info = (array)Db::table('customer_addresses')->addSelect(['id','default_address'])->where([
            ['customer_id','=',$customer_id],
            ['default_address','=',1],
        ])->first();

        if($count == 0){
            if($default_address== null){
                $default_address = 1;
            }else{
                $default_address = 0;
            }
        }else{
            if($default_address == null){
                $default_address = 0;
            }else{
                if(!empty($address_info) && $default_address == 1){
                    Db::table('customer_addresses')->where('id','=',$address_info['id'])->update(
                        ['default_address'=>0]
                    );
                }
            }
        }

        $time = date("Y-m-d H:i:s");
        $result = Db::table('customer_addresses')->insert(
            ['customer_id'=>$customer_id,'country'=>$country,'state'=>$state,'city'=>$city,'address1'=>$address1,'postcode'=>$postcode,'phone'=>$phone,'default_address'=>$default_address,'created_at'=>$time,'updated_at'=>$time]
        );

        return $result;
    }


    /**
     * 设置订单状态
     *
     * @author 邹柯
     * @param $order_id int 是 订单id
     * @param $status int 是 订单状态:2-取消订单、4-确认收货
     * @return int
     */
    public static function setOrderStatus($order_id,$status){
        return DB::table('orders')->where('increment_id',$order_id)->update(['status'=>$status]);
    }

    /**
     * 删除订单
     *
     * @author 邹柯
     * @param $order_id int 是 订单id
     * @return int
     */
    public static function deleteOrder($order_id){
        return DB::table('orders')->where('increment_id',$order_id)->update(['is_deleted'=>1]);
    }
}
