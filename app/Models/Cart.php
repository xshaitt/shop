<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Class Cart
 *
 * @author 邹柯
 * @package App\Models
 */
class Cart extends Model
{
    /**
     * 获取快递方式
     *
     * @param $cart_id
     * @return array|\Illuminate\Support\Collection
     */
    public static function getShipping($cart_id){
        $result = Db::table('cart_address as ca')
            ->addSelect([
                'csr.carrier',
                'csr.carrier_title',
                'csr.method',
                'csr.method_title',
                'csr.method_description',
                'csr.price'])
            ->leftJoin('cart_shipping_rates as csr','ca.id','=','=csr.cart_address_id')
            ->where('ca.cart_id',$cart_id)->get();

        if(!empty($result)){
            $result = object_to_array($result);
        }else{
            $result = [];
        }

        return $result;
    }


    /**
     * 加入购物车
     *
     * @param $seller_id int 是 店铺id
     * @param $customer_id int 是 客户id
     * @param $product_id int 是 商品id
     * @param $product_attribute_id int 是 商品属性id
     * @return bool|int
     */
    public static function addCart($seller_id,$customer_id,$product_id,$product_attribute_id){
        $cart_info = (array)DB::table('product_cart')->where([
            'seller_id'=>$seller_id,
            'customer_id'=>$customer_id,
            'product_id'=>$product_id,
            'product_attribute_id'=>$product_attribute_id
        ])->first();

        if(empty($cart_info)){
            return DB::table('product_cart')->insert([
                'seller_id'=>$seller_id,
                'customer_id'=>$customer_id,
                'count'=>1,
                'product_id'=>$product_id,
                'product_attribute_id'=>$product_attribute_id,
                'created_at'=>date("Y-m-d H:i:s")
            ]);
        }else{
            return DB::table('product_cart')->where([
                ['seller_id',$seller_id],
                ['customer_id',$customer_id],
                ['product_id',$product_id],
                ['product_attribute_id',$product_attribute_id],
            ])->increment('count');
        }

    }

    /**
     * 增加商品数量
     *
     * @param $product_cart_id int 是 购物车id
     * @return int
     */
    public static function upCart($product_cart_id,$count = 0){
        return DB::table('product_cart')->where('id','=',$product_cart_id)->increment('count',$count);
    }

    /**
     * 减少商品数量
     *
     * @param $product_cart_id int 是 购物车id
     * @return int
     */
    public static function downCart($product_cart_id,$count = 0){
        //获取购物车商品数量
        $cart_info = self::getCartGoodsByCartId($product_cart_id);
        if($cart_info['count'] < $count){
            $count = $cart_info['count'];
        }
        return DB::table('product_cart')->where('id','=',$product_cart_id)->decrement('count',$count);
    }

    /**
     * 删除购物车商品
     *
     * @param $product_cart_ids string 是 购物车id列表
     * @return int
     */
    public static function delCart($product_cart_ids){
        return DB::table('product_cart')->whereIn('id',[$product_cart_ids])->delete();
    }

    /**
     * 获取购物车商品信息
     *
     * @param $product_cart_id int 是 购物车id
     * @return mixed
     */
    public static function getCartGoodsByCartId($product_cart_id){
        return (array)DB::table('product_cart')->addSelect(['count','product_attribute_id'])->where('id','=',$product_cart_id)->first();
    }

    /**
     * 获取商品库存
     *
     * @param $product_attribute_id int 是 商品属性id
     * @return mixed
     */
    public static function getCartGoodsInventory($product_attribute_id){
        $product_id = Goods::getProductIdByProductAttributeId($product_attribute_id);
        return DB::table('product_inventories')->where('product_id','=',$product_id)->sum('qty');
    }


    /**
     * 获取购物车商品id
     *
     * @param $seller_id int 是 店铺id
     * @param $customer_id int 是 客户id
     * @return array|\Illuminate\Support\Collection
     */
    public static function getCartProductAttributeIds($seller_id,$customer_id){
        $result = DB::table('product_cart')->select('product_attribute_id','count')->where([
            ['seller_id',$seller_id],
            ['customer_id',$customer_id],
        ])->get();

        if(!empty($result)){
            $result = object_to_array($result);
        }else{
            $result = [];
        }

        return $result;
    }
}
