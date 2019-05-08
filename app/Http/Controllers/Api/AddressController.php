<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Service\ApiService;
use App\Models\Address;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * @title 收货地址管理
 * @class Address
 * @auth 邹柯
 * @date 2019/05/07
 */
class AddressController extends Controller
{
    /**
     * @title 收货地址列表
     * @desc  {"0":"接口地址：/api/address/list","1":"请求方式：GET","2":"开发者: 邹柯"}
     * @param {"name":"page","type":"int","required":false,"desc":"页码,不传默认1","level":1}
     * @param {"name":"page_size","type":"int","required":false,"desc":"每页显示条数，不传默认5","level":1}
     * @return {"name":"code","type":"int","required":true,"desc":"返回码：0成功,-1失败","level":1}
     * @return {"name":"data","type":"","required":true,"desc":"","level":1}
     * @return {"name":"page","type":"int","required":true,"desc":"页码","level":2}
     * @return {"name":"page_size","type":"int","required":true,"desc":"每页显示条数","level":2}
     * @return {"name":"total_page_sizes","type":"int","required":true,"desc":"总页数","level":2}
     * @return {"name":"result","type":"dict","required":true,"desc":"收货地址信息","level":2}
     * @return {"name":"customer_name","type":"string","required":true,"desc":"收货人姓名","level":3}
     * @return {"name":"phone","type":"string","required":true,"desc":"收货人电话","level":3}
     * @return {"name":"detail","type":"string","required":true,"desc":"收货人地址","level":3}
     * @example {"code":0,"errCode":200,"message":"加载成功","data":{"page":"1","page_size":"2","total_page_sizes":1,"result":{"page":"1","page_size":"2","total_page_sizes":1,"result":[{"customer_name":"zou ke","phone":"18761939022","detail":"ADqqqqqq q"},{"customer_name":"zou ke","phone":"17721355485","detail":"CN上海市上海市 宝山区和家欣苑A区5栋101"}]}}}
     */
    public function addressList(Request $request){
        //参数校验
        $messages = [
            'page.numeric'        => 40001,
            'page_size.numeric'   => 40002,
        ];
        $validator = Validator::make($request->all(), [
            'page'      => 'bail|nullable|numeric',
            'page_size' => 'bail|nullable|numeric',
        ],$messages);
        
        if ($validator->fails()) {
            return ApiService::error($validator->errors()->first());
        }

        //获取接收参数
        $data = $request->input();
        $page = empty($data['page']) ? 1: $data['page'];
        $page_size = empty($data['page_size']) ? 5: $data['page_size'];
        $customer_id = 2;

        //获取收货地址列表
        $result = Address::getAddressList($customer_id,$page,$page_size);


        return ApiService::success(['page'=>$result['page'],'page_size'=>$result['page_size'],'total_page_sizes'=>$result['total_page_sizes'],'result'=>$result]);
    }


    /**
     * @title 收货地址详情
     * @desc  {"0":"接口地址：/api/address/detail","1":"请求方式：GET","2":"开发者: 邹柯"}
     * @param {"name":"address_id","type":"int","required":true,"desc":"收货地址id","level":1}
     * @return {"name":"code","type":"int","required":true,"desc":"返回码：0成功,-1失败","level":1}
     * @return {"name":"data","type":"","required":true,"desc":"","level":1}
     * @return {"name":"customer_id","type":"int","required":true,"desc":"客户id","level":2}
     * @return {"name":"country","type":"string","required":true,"desc":"国家","level":2}
     * @return {"name":"state","type":"string","required":true,"desc":"省/州","level":2}
     * @return {"name":"city","type":"string","required":true,"desc":"城市","level":2}
     * @return {"name":"address1","type":"string","required":true,"desc":"街道地址","level":2}
     * @return {"name":"postcode","type":"string","required":false,"desc":"邮政编码","level":2}
     * @return {"name":"phone","type":"string","required":true,"desc":"电话","level":2}
     * @return {"name":"default_address","type":"string","required":true,"desc":"是否默认地址:1是、0否","level":2}
     * @example {"code":0,"errCode":200,"message":"加载成功","data":{"customer_id":2,"address1":"宝山区和家欣苑A区5栋101","country":"CN","state":"上海市","city":"上海市","postcode":233700,"phone":"17721355485","default_address":1}}
     */
    public function addressDetail(Request $request){
        //参数校验
        $messages = [
            'address_id.required'       => 41004,
            'address_id.numeric'        => 42004
        ];
        $validator = Validator::make($request->all(), [
            'address_id'      => 'required|numeric',
        ],$messages);

        if ($validator->fails()) {
            return ApiService::error($validator->errors()->first());
        }

        //获取接收参数
        $data = $request->input();
        //获取收货地址详情
        $result = Address::getAddressDetail($data['address_id']);

        return ApiService::success($result);
    }


    /**
     * @title 国家列表
     * @desc  {"0":"接口地址：/api/country/list","1":"请求方式：GET","2":"开发者: 邹柯"}
     * @return {"name":"code","type":"int","required":true,"desc":"返回码：0成功,-1失败","level":1}
     * @return {"name":"data","type":"","required":true,"desc":"","level":1}
     * @return {"name":"id","type":"int","required":true,"desc":"唯一自增id","level":2}
     * @return {"name":"code","type":"string","required":true,"desc":"国家编码","level":2}
     * @return {"name":"name","type":"string","required":true,"desc":"国家名称","level":2}
     * @example {"code":0,"errCode":200,"message":"加载成功","data":[{"id":1,"code":"AF","name":"Afghanistan"},{"id":2,"code":"AX","name":"Åland Islands"}]}
     */
    public function countryList(){
        //获取国家列表
        $result = Address::getCountryList();

        return ApiService::success($result);
    }


    /**
     * @title 修改收货地址
     * @desc  {"0":"接口地址：/api/address/update","1":"请求方式：POST","2":"开发者: 邹柯"}
     * @param {"name":"customer_id","type":"int","required":true,"desc":"客户id"}
     * @param {"name":"address_id","type":"int","required":true,"desc":"收货地址id"}
     * @param {"name":"country","type":"string","required":true,"desc":"国家名称"}
     * @param {"name":"state","type":"string","required":true,"desc":"省/州"}
     * @param {"name":"city","type":"string","required":true,"desc":"城市"}
     * @param {"name":"address1","type":"string","required":true,"desc":"街道地址"}
     * @param {"name":"postcode","type":"string","required":true,"desc":"邮政编码"}
     * @param {"name":"phone","type":"string","required":true,"desc":"电话"}
     * @param {"name":"default_address","type":"int","required":false,"desc":"是否默认收货地址:1是、0否"}
     * @return {"name":"code","type":"int","required":true,"desc":"返回码：0成功,-1失败","level":1}
     * @return {"name":"data","type":"int","required":true,"desc":"修改成功的记录数","level":1}
     * @example {"code":0,"errCode":200,"message":"加载成功","data":1}
     */
    public function updateAddress(Request $request){
        //参数校验
        $messages = [
            'customer_id.required'       => 41010,
            'customer_id.numeric'        => 42006,
            'address_id.required'        => 41004,
            'address_id.numeric'         => 42004,
            'country.required'           => 41005,
            'state.required'             => 41006,
            'city.required'              => 41007,
            'address1.required'          => 41008,
            'phone.required'             => 41009,
            'default_address.in'         => 42005,
        ];
        $validator = Validator::make($request->all(), [
            'customer_id'     => 'bail|required|numeric',
            'address_id'      => 'bail|required|numeric',
            'country'         => 'required',
            'state'           => 'required',
            'city'            => 'required',
            'address1'        => 'required',
            'postcode'        => 'nullable',
            'default_address' => 'bail|nullable|in:0,1',
        ],$messages);

        if ($validator->fails()) {
            return ApiService::error($validator->errors()->first());
        }

        //获取接收参数
        $data = $request->input();
        if(!isset($data['postcode'])){
            $data['postcode'] = null;
        }
        $postcode = empty($data['postcode']) ? 0: $data['postcode'];
        if(!isset($data['default_address'])){
            $data['default_address'] = null;
        }
        $default_address =  empty($data['default_address']) ? null : $data['default_address'];
        //修改收货地址
        $result = Address::updateAddress($data['customer_id'],$data['address_id'],$data['country'],$data['state'],$data['city'],$data['address1'],$postcode,$data['phone'],$default_address);

        return ApiService::success($result);
    }

    /**
     * @title 添加收货地址
     * @desc  {"0":"接口地址：/api/address/create","1":"请求方式：POST","2":"开发者: 邹柯"}
     * @param {"name":"customer_id","type":"int","required":true,"desc":"客户id"}
     * @param {"name":"country","type":"string","required":true,"desc":"国家名称"}
     * @param {"name":"state","type":"string","required":true,"desc":"省/州"}
     * @param {"name":"city","type":"string","required":true,"desc":"城市"}
     * @param {"name":"address1","type":"string","required":true,"desc":"街道地址"}
     * @param {"name":"postcode","type":"string","required":true,"desc":"邮政编码"}
     * @param {"name":"phone","type":"string","required":true,"desc":"电话"}
     * @param {"name":"default_address","type":"int","required":false,"desc":"是否默认收货地址:1是、0否"}
     * @return {"name":"code","type":"int","required":true,"desc":"返回码：0成功,-1失败","level":1}
     * @return {"name":"data","type":"int","required":true,"desc":"添加成功的记录数","level":1}
     * @example {"code":0,"errCode":200,"message":"加载成功","data":1}
     */
    public function createAddress(Request $request){
        //参数校验
        $messages = [
            'customer_id.required'       => 41010,
            'customer_id.numeric'        => 42006,
            'country.required'           => 41005,
            'state.required'             => 41006,
            'city.required'              => 41007,
            'address1.required'          => 41008,
            'phone.required'             => 41009,
            'default_address.in'         => 42005,
        ];
        $validator = Validator::make($request->all(), [
            'customer_id'     => 'bail|required|numeric',
            'country'         => 'required',
            'state'           => 'required',
            'city'            => 'required',
            'address1'        => 'required',
            'phone'           => 'required',
            'postcode'        => 'nullable',
            'default_address' => 'bail|nullable|in:0,1',
        ],$messages);

        if ($validator->fails()) {
            return ApiService::error($validator->errors()->first());
        }

        //获取接收参数
        $data = $request->input();
        if(!isset($data['postcode'])){
            $data['postcode'] = null;
        }
        $postcode = empty($data['postcode']) ? 0: $data['postcode'];
        if(!isset($data['default_address'])){
            $data['default_address'] = null;
        }
        $default_address =  empty($data['default_address']) ? null : $data['default_address'];
        //添加收货地址
        $result = Address::createAddress($data['customer_id'],$data['country'],$data['state'],$data['city'],$data['address1'],$postcode,$data['phone'],$default_address);

        return ApiService::success($result);
    }


    /**
     * @title 设置默认收货地址
     * @desc  {"0":"接口地址：/api/address/setDefault","1":"请求方式：GET","2":"开发者: 邹柯"}
     * @param {"name":"customer_id","type":"int","required":true,"desc":"客户id"}
     * @param {"name":"address_id","type":"int","required":true,"desc":"收货地址id"}
     * @return {"name":"code","type":"int","required":true,"desc":"返回码：0成功,-1失败","level":1}
     * @return {"name":"data","type":"int","required":true,"desc":"设置成功的记录数","level":1}
     * @example {"code":0,"errCode":200,"message":"加载成功","data":1}
     */
    public function setDefaultAddress(Request $request){
        //参数校验
        $messages = [
            'customer_id.required'       => 41010,
            'customer_id.numeric'        => 42006,
            'address_id.required'        => 41004,
            'address_id.numeric'         => 42004,
        ];
        $validator = Validator::make($request->all(), [
            'customer_id'     => 'bail|required|numeric',
            'address_id'      => 'bail|required|numeric',
        ],$messages);

        if ($validator->fails()) {
            return ApiService::error($validator->errors()->first());
        }
        //获取接收参数
        $data = $request->input();
        //修改收货地址
        $result = Address::setDefaultAddress($data['customer_id'],$data['address_id']);

        return ApiService::success($result);
    }
}