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


    public static function getCart($cart_id){

    }
}
