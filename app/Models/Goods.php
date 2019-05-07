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

    /**
     * 获取商品详情
     *
     * @author 邹柯
     * @param $product_id int 商品id
     * @param $locale string 是 本地化
     * @return Model|\Illuminate\Database\Query\Builder|object|null
     */
    public static function getGoodsDetail($product_id,$locale = 'zh-cn'){
        //获取商品详情
        $result = Db::table('product_flat')->addSelect(['product_id','description','new','featured','status','visible_individually'])->where([
            ['product_id','=',$product_id],
            ['locale','=',$locale],
        ])->first();
        if(!empty($result)){
            $result = (array)$result;

            //获取商品图片
            $product_images = self::getGoodsImageByProductIds([$product_id]);
            if(!empty($product_images)){
                foreach($product_images as $k=>$v){
                    $p_imgs[$v['product_id']] = $v['image_paths'];
                }
                $result['image_paths'] = $p_imgs[$v['product_id']];
            }else{
                $result['image_paths'] = null;
            }
        }else{
            $result = [];
        }

        return $result;
    }

    /**
     * 获取商品sku属性
     *
     * @author 邹柯
     * @param $product_id int 是 商品id
     * @param $locale string 是 本地化
     * @return mixed
     */
    public static function getGoodsSkuAttribute($product_id,$product_attribute_id = null,$locale = 'zh-cn'){
        //本地化商品id
        $locate_product_id = self::getGoodsLocaleSkuId($product_id,$locale);

        $result = Db::table('product_flat')->addSelect(['id as product_attribute_id','status','name as goods_name',DB::raw('concat_ws(" ",concat("颜色:",color_label),concat("尺码:",size_label)) as attributes'),'price'])
            ->where([
                ['parent_id','=',$locate_product_id],
            ])->get();

        if(!empty($result)){
            $result = object_to_array($result);
            foreach($result as $k=>$v){
                if($product_attribute_id == $v['product_attribute_id']){
                    $result[$k]['is_selected'] = 1;
                }else{
                    $result[$k]['is_selected'] = 0;
                }
            }

        }

        return $result;
    }

    /**
     * 获取本地化商品id
     *
     * @author 邹柯
     * @param $product_id int 是 商品id
     * @param $locale string 是 本地化
     * @return mixed
     */
    private static function getGoodsLocaleSkuId($product_id,$locale = 'zh-cn'){
        $result = Db::table('product_flat')
            ->where([
                ['product_id','=',$product_id],
                ['locale','=',$locale],
                ['parent_id','=',null]
            ])->value('id');

        return $result;
    }

    /**
     * 获取商品id
     *
     * @author 邹柯
     * @param $locate_product_id int 是 本地化商品id
     * @param $locale string 是 本地化
     * @return mixed
     */
    private static function getGoodsSkuId($locate_product_id){
        $result = Db::table('product_flat')
            ->where([
                ['id','=',$locate_product_id]
            ])->value('product_id');

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
