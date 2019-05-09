<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Class Address
 *
 * @author 邹柯
 * @package App\Models
 */
class Address extends Model
{
    /**
     * 收货地址列表
     *
     * @author 邹柯
     * @param $customer_id int 是 客户id
     * @param $page int 是 页码
     * @param $page_size int 是 每页显示条数
     * @return mixed
     */
    public static function getAddressList($customer_id,$page,$page_size){
        $offset = ($page - 1) * $page_size;
        //总记录数
        $count = Db::table('customer_addresses')->where('customer_id','=',$customer_id)->count();
        //总页数
        $total_page_sizes = ceil($count/$page_size);

        $result = Db::table('customer_addresses as ca')->addSelect(['ca.id as address_id','ca.customer_id','ca.address1','ca.country','ca.state','ca.city','ca.postcode','ca.phone','ca.default_address','c.first_name','c.last_name'])
            ->leftJoin('customers as c','ca.customer_id','=','c.id')
            ->where('customer_id','=',$customer_id)->offset($offset)->limit($page_size)->orderBy('default_address','DESC')->get();

        if(!empty($result)){
            $result = object_to_array($result);
            foreach($result as $k=>$v){
                $address[$k]['customer_name'] = $v['first_name']." ".$v['last_name'];
                $address[$k]['phone'] = $v['phone'];
                $address[$k]['detail'] = $v['country'].$v['state'].$v['city']." ".$v['address1'];
            }
        }else{
            $address = null;
        }


        return ['page'=>$page,'page_size'=>$page_size,'total_page_sizes'=>$total_page_sizes,'result'=>$address];
    }

    /**
     * 收货地址详情
     *
     * @author 邹柯
     * @param $address_id int 是 收货地址id
     * @return array|Model|\Illuminate\Database\Query\Builder|object|null
     */
    public static function getAddressDetail($address_id){
        $result = (array)Db::table('customer_addresses')->addSelect(['customer_id','address1','address2','country','state','city','postcode','phone','default_address'])
            ->where('id','=',$address_id)->first();

        return $result;
    }

    /**
     * 客户默认收货地址
     *
     * @author 邹柯
     * @param $customer_id int 是 客户id
     * @return array|Model|\Illuminate\Database\Query\Builder|object|null
     */
    public static function getAddressDefault($customer_id){
        $result = (array)Db::table('customer_addresses')->addSelect(['customer_id','address1','country','state','city','postcode','phone','default_address'])
            ->where([
                ['default_address','=',1],
                ['customer_id','=',$customer_id]
            ])->first();

        return $result;
    }


    /**
     * 国家列表
     *
     * @author 邹柯
     * @return array|Illuminate\Support\Collection
     */
    public static function getCountryList(){
        $result = Db::table('countries')->addSelect(['id','code','name'])->get();

        if(!empty($result)){
            $result = object_to_array($result);
        }

        return $result;
    }

    /**
     * 修改收货地址
     *
     * @author 邹柯
     * @param $customer_id int 是 客户id
     * @param $address_id int 是 收货地址id
     * @param $country string 是 国家
     * @param $state string 是 省/州
     * @param $city string 是 城市
     * @param $address1 string 是 街道地址
     * @param $postcode string 是 邮政编码
     * @param $default_address int 是 是否默认:1是、0否
     * @return boolean
     */
    public static function updateAddress($customer_id,$address_id,$country = "",$state = "",$city = "",$address1 = "",$postcode = 0,$phone = "",$default_address = null){
        $address_info = (array)Db::table('customer_addresses')->addSelect(['id','default_address'])->where([
            ['customer_id','=',$customer_id],
            ['id','<>',$address_id],
            ['default_address','=',1]
        ])->first();

        if($default_address <> null){
            if(!empty($address_info) && $default_address == 1){
                Db::table('customer_addresses')->where('id','=',$address_info['id'])->update(
                    ['default_address'=>0]
                );
            }
        }else{
            $default_address = Db::table('customer_addresses')->where('id','=',$address_id)->value('default_address');
        }

        $result = Db::table('customer_addresses')->where('id','=',$address_id)->update(
            ['country'=>$country,'state'=>$state,'city'=>$city,'address1'=>$address1,'postcode'=>$postcode,'phone'=>$phone,'default_address'=>$default_address,'updated_at'=>date("Y-m-d H:i:s")]
        );

        return $result;
    }


    /**
     * 添加收货地址
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
     * 设置默认收货地址
     *
     * @author 邹柯
     * @param $customer_id int 是 客户id
     * @param $address_id int 是 收货地址id
     * @param int $default_address 默认1
     * @return int
     */
    public static function setDefaultAddress($customer_id,$address_id,$default_address = 1){
        $address_info = (array)Db::table('customer_addresses')->addSelect(['id','default_address'])->where([
            ['customer_id','=',$customer_id],
            ['id','<>',$address_id],
            ['default_address','=',1]
        ])->first();

        if(!empty($address_info) && $default_address == 1){
            Db::table('customer_addresses')->where('id','=',$address_info['id'])->update(
                ['default_address'=>0]
            );
        }
        return Db::table('customer_addresses')->where('id','=',$address_id)->update(
            ['default_address'=>1]
        );

    }
}
