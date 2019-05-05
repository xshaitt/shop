<?php


namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class GoodsController extends Controller
{
    /**
     * 获取商品详情页
     *
     * @param
     * @param
     */
    public function goodsDetail(Request $request){
        $this->validate(Request(),[
            'id' => 'required|numeric'
        ]);

        //获取接收参数
        $data = $request->input();
        $id = $data['id'];

        $result = Db::table('products')->get();


        $response['code'] = 0;
        $response['message'] = trans('shop::app.customer.signup-form.failed');
        $response['result'] = $result;

        return $response;
    }
}