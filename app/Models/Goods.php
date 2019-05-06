<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Class Goods
 *
 * @author 邹柯
 * @package App\Models
 */
class Goods extends Model
{
    /**
     * 根据店铺id获取商品列表
     *
     * @author 邹柯
     * @param $seller_id int 是 店铺id
     * @param $page int 是 页码
     * @param $page_size int 是 每页显示条数
     * @return mixed
     */
    public static function getGoodsByPage($seller_id,$page,$page_size){
        $offset = ($page - 1) * $page_size;

        $count = Db::table('products as p')->addSelect(['pg.product_id','pg.name','pg.quantity','pg.price'])
            ->leftJoin('products_grid as pg','p.id','=','pg.product_id')
            ->where([
                ['pg.status','=',1],
                ['p.seller_id','=',$seller_id],
                ['p.parent_id','=',null]
            ])->count();

        $total_page_sizes = ceil($count/$page_size)+1;

        $result = Db::table('products as p')->addSelect(['pg.product_id','pg.name','pg.quantity','pg.price'])
            ->leftJoin('products_grid as pg','p.id','=','pg.product_id')
            ->where([
                ['pg.status','=',1],
                ['p.seller_id','=',$seller_id],
                ['p.parent_id','=',null]
            ])->offset($offset)->limit($page_size)->get();

        if(!empty($result)){
            $result = object_to_array($result);
        }

        return ['page'=>$page,'page_size'=>$page_size,'total_page_sizes'=>$total_page_sizes,'result'=>$result];
    }


    public static function getGoodsDetail($product_id){
        //获取商品详情
        $result = Db::table('products_grid as pg')->addSelect(['pg.name','quantity','price'])
            ->leftJoin('products_flat as pf','pg.product_id','=','pf.product_id')
            ->where([
                ['pg.status','=',1],
            ])->where('product_id','=',$product_id)->first();

        return $result;
    }

    public static function getGoodsAttribute($product_id,$locale = 'zh-cn'){
        $result = Db::table('products_grid as pg')->addSelect(['pg.name','pf.price','price'])
            ->leftJoin('products_flat as pf','pg.product_id','=','pf.product_id')
            ->where([
                ['pg.status','=',1],
                ['pg.locale','=',$locale],
            ])->where('product_id','=',$product_id)->get();

        return $result;
    }


    /**
     * 根据商品id获取商品图片
     *
     * @author 邹柯
     * @param $product_ids array 是 商品id
     * @return mixed
     */
    public static function getGoodsImageByProductIds($product_ids){
        $result = Db::table('product_images')->addSelect(['product_id',DB::raw('group_concat(path) as image_paths')])
            ->whereIn('product_id',$product_ids)
            ->get();

        if(!empty($result)){
            $result = object_to_array($result);
        }

        return $result;
    }


    /**
     * 根据商品id获取商品的分类
     *
     * @author 邹柯
     * @param $product_ids array 是 商品id
     * @return mixed
     */
    public static function getGoodsCategoryByProductIds($product_ids){
        //打印sql
        //DB::connection()->enableQueryLog();
        $result = Db::table('product_categories as pc')->addSelect(['ct.name','pc.product_id','pc.category_id'])
            ->leftJoin('category_translations as ct','pc.category_id','=','ct.id')
            ->whereIn('pc.product_id',$product_ids)
            ->get();
        //$log = DB::getQueryLog();
        //var_dump($log);

        if(!empty($result)){
            $result = object_to_array($result);
        }

        return $result;
    }
}
