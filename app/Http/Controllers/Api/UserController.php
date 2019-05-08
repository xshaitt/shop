<?php

namespace App\Http\Controllers\Api;

use App\Http\Service\ApiService;
use App\Models\Goods;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Webkul\Customer\Repositories\CustomerRepository;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;
use Webkul\Customer\Mail\VerificationEmail;

/**
 * @title 用户管理
 * @class User
 * @auth 邹柯
 * @date 2019/05/08
 */
class UserController extends Controller
{
    protected $customer;

    public function __construct(CustomerRepository $customer)
    {
        $this->customer = $customer;
    }

    /**
     * @title 用户信息
     * @desc  {"0":"接口地址：/user/register","1":"请求方式：POST","2":"开发者: 帅华"}
     */
    public function createUser(Request $request)
    {
        $data = request()->input();
        $data['password'] = 'x123456';
        $data['first_name'] = '';
        $data['last_name'] = '';
        $data['password'] = bcrypt($data['password']);
        $data['channel_id'] = core()->getCurrentChannel()->id;

        $data['is_verified'] = 1;

        $data['customer_group_id'] = 1;

        $verificationData['email'] = $data['email'];
        $verificationData['token'] = md5(uniqid(rand(), true));
        $data['token'] = $verificationData['token'];

        Event::fire('customer.registration.before');

        $customer = $this->customer->create($data);

        Event::fire('customer.registration.after', $customer);
        $response = [
            'code' => 200,
            'message' => ''
        ];
        if ($customer) {
            try {
//                session()->flash('success', trans('shop::app.customer.signup-form.success'));

                Mail::send(new VerificationEmail($verificationData));
            } catch (\Exception $e) {
//                session()->flash('info', trans('shop::app.customer.signup-form.success-verify-email-not-sent'));

                $response['code'] = 200;
                $response['message'] = trans('shop::app.customer.signup-form.success-verify-email-not-sent');
            }

        } else {

//            session()->flash('error', trans('shop::app.customer.signup-form.failed'));

            $response['code'] = 500;
            $response['message'] = trans('shop::app.customer.signup-form.failed');
        }
        return $response;
    }


    /**
     * @title 用户信息
     * @desc  {"0":"接口地址：/user/info","1":"请求方式：GET","2":"开发者: 邹柯"}
     * @return {"name":"code","type":"int","required":true,"desc":"返回码：0成功,-1失败","level":1}
     * @return {"name":"data","type":"","required":true,"desc":"","level":1}
     * @return {"name":"first_name","type":"int","required":true,"desc":"姓名","level":2}
     * @return {"name":"gender","type":"int","required":true,"desc":"性别:1男、0女","level":2}
     * @return {"name":"date_of_birth","type":"string","required":true,"desc":"出生日期","level":2}
     * @return {"name":"email","type":"string","required":true,"desc":"邮箱","level":2}
     * @return {"name":"phone","type":"string","required":true,"desc":"手机号","level":2}
     * @example {"code":0,"errCode":200,"message":"加载成功","data":{"first_name":"zou","last_name":"ke","gender":"1","date_of_birth":"1990-12-20","email":"zouke1220@126.com","phone":"17721355485"}}
     */
    public function userInfo(){
        $customer_id = 2;

        //获取用户信息
        $user_info = User::getUser($customer_id);

        return ApiService::success($user_info);
    }


    /**
     * @title 修改用户信息
     * @desc  {"0":"接口地址：/user/update","1":"请求方式：POST","2":"开发者: 邹柯"}
     * @param {"name":"first_name","type":"string","required":true,"desc":"用户名"}
     * @param {"name":"gender","type":"int","required":true,"desc":"性别"}
     * @param {"name":"date_of_birth","type":"date","required":true,"desc":"出生日期"}
     * @param {"name":"email","type":"string","required":true,"desc":"邮箱"}
     * @return {"name":"code","type":"int","required":true,"desc":"返回码：0成功,-1失败","level":1}
     * @return {"name":"data","type":"int","required":true,"desc":"修改成功的记录数","level":1}
     * @example {"code":0,"errCode":200,"message":"加载成功","data":1}
     */
    public function updateUser(Request $request){
        //参数校验
        $messages = [
            'customer_id.required'       => 41010,
            'customer_id.numeric'        => 42006,
            'gender.required'            => 41011,
            'gender.numeric'             => 42007,
            'date_of_birth.required'     => 41012,
            'date_of_birth.date'         => 42008,
            'email.required'             => 41013,
            'email.email'                => 42009,
        ];
        $validator = Validator::make($request->all(), [
            'customer_id'     => 'required|numeric',
            'gender'          => 'required|numeric|in:0,1',
            'date_of_birth'   => 'required|date',
            'email'           => 'required|email',
        ],$messages);

        if ($validator->fails()) {
            return ApiService::error($validator->errors()->first());
        }

        //获取接收参数
        $data = $request->input();
        $customer_id = 2;

        //修改用户信息
        $result = User::updateUser($customer_id,$data['first_name'],$data['gender'],$data['date_of_birth'],$data['email']);

        return ApiService::success($result);
    }
}
