<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Class User
 *
 * @author 邹柯
 * @package App\Models
 */
class User extends Model
{
    /**
     * 获取用户信息
     *
     * @author 邹柯
     * @param $customer_id int 是 用户id
     * @return array|Model|\Illuminate\Database\Query\Builder|object|null
     */
    public static function getUser($customer_id){
        $result = (array)Db::table('customers')->addSelect(['first_name','last_name','gender','date_of_birth','email','phone'])
            ->where('id','=',$customer_id)->first();

        return $result;
    }

    /**
     * 修改用户信息
     *
     * @author 邹柯
     * @param $customer_id int 是 用户id
     * @param $first_name string 是 用户姓名
     * @param $gender int 是 性别:1男、0女
     * @param $date_of_birth date 是 生日
     * @param $email string 是 邮箱
     * @return int
     */
    public static function updateUser($customer_id,$first_name,$gender,$date_of_birth,$email){
        $result = Db::table('customers')->where('id',$customer_id)
            ->update(['first_name'=>$first_name,'gender'=>$gender,'date_of_birth'=>$date_of_birth,'email'=>$email]);

        return $result;
    }
}