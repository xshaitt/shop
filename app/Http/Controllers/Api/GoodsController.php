<?php


namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class GoodsController extends Controller
{
    /**
     * 商品列表
     *
     * @param seller_id 是 int 店铺id
     */
    public function goodsList(Request $request){
        //参数校验
        $this->validate(Request(),[
            'seller_id' => 'required|numeric'
        ]);

        //获取接收参数
        $data = $request->input();
        $seller_id = $data['seller_id'];
        //获取商品列表
        $result = Db::table('products as p')->addSelect(['pg.name','pg.quantity','pg.price'])
            ->leftJoin('products_grid as pg','p.id','=','pg.product_id')
            ->where([
            ['pg.status','=',1],
            ['p.seller_id','=',$seller_id],
            ['p.parent_id','=',null]
        ])->get();

        //返回数据处理
        $response['code'] = 0;
        $response['err_code'] = 20001;
        $response['message'] = config('code.code.zh_cn.'.$response['err_code']);;
        $response['result'] = $result;

        return $response;
    }

    /**
     * 商品详情
     *
     * @param id 是 int 商品id
     */
    public function goodsDetail(Request $request){
        //参数校验
        $this->validate(Request(),[
            'product_id' => 'required|numeric'
        ]);

        //获取接收参数
        $data = $request->input();
        $product_id = $data['product_id'];
        //获取商品详情
        $result = Db::table('products_grid')->addSelect(['name','quantity','price'])->where('product_id','=',$product_id)->first();

        //返回数据处理
        $response['code'] = 0;
        $response['err_code'] = 20000;
        $response['message'] = config('code.code.zh_cn.'.$response['err_code']);
        $response['result'] = $result;

        return $response;
    }



}