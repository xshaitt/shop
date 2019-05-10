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
    public static function getGoodsList($seller_id,$page,$page_size,$channel_info){
        $offset = ($page - 1) * $page_size;

        $count = Db::table('product_flat as pf')->leftJoin('products as p','pf.product_id','=','p.id')
            ->where([
                ['pf.status','=',1],
                ['p.seller_id','=',$seller_id],
                ['pf.parent_id','=',null],
                ['pf.locale','=',$channel_info['locale_code']],
                ['pf.channel','=',$channel_info['channel_code']]
            ])->count();
        $total_page_sizes = ceil($count/$page_size);

        $result = Db::table('product_flat as pf')->addSelect(['pf.product_id','pf.name','pf.price'])
            ->leftJoin('products as p','pf.product_id','=','p.id')
            ->where([
                ['pf.status','=',1],
                ['p.seller_id','=',$seller_id],
                ['pf.parent_id','=',null],
                ['pf.locale','=',$channel_info['locale_code']],
                ['pf.channel','=',$channel_info['channel_code']]
            ])->offset($offset)->limit($page_size)->get();

        if(!empty($result)){
            $result = object_to_array($result);
        }else{
            $result = null;
        }

        return ['page'=>$page,'page_size'=>$page_size,'total_page_sizes'=>$total_page_sizes,'result'=>$result];
    }

    /**
     * 获取商品详情
     *
     * @author 邹柯
     * @param $product_ids int 是 商品id列表
     * @param $product_attribute_ids int 是 商品属性id列表
     * @param string $locale string 是 本地化
     * @return array
     */
    public static function getGoodsDetail($product_ids,$product_attribute_ids,$channel_info){
        $result = self::getGoodsAttributes($product_attribute_ids);
        if(!empty($result)){
            //获取商品信息
            $result_product = self::getGoodsByProductIds($product_ids,$channel_info);
            foreach($result_product as $k=>$v){
                $product_info = $v;
                unset($product_info['id']);
                $p[$v['id']] = $product_info;
            }
            //获取商品图片
            $product_images = self::getGoodsImageByProductIds($product_ids);
            //根据上级id获取商品id
            $switch_res = self::getProductIdByParentId(array_unique(array_column($result,'parent_id')));
            if(!empty($product_images)){
                foreach($result as $k=>$v){
                    $result[$k]['image_paths'] = isset($product_images[$switch_res[$v['parent_id']]])? $product_images[$switch_res[$v['parent_id']]] : null;
                    $result[$k]['description'] = $p[$v['parent_id']]['description'];
                    $result[$k]['new'] = $p[$v['parent_id']]['new'];
                    $result[$k]['featured'] = $p[$v['parent_id']]['featured'];
                    $result[$k]['visible_individually'] = $p[$v['parent_id']]['visible_individually'];
                    $result[$k]['product_id'] = $p[$v['parent_id']]['product_id'];
                }
            }else{
                foreach($result as $k=>$v){
                    $result[$k]['image_paths'] = null;
                    $result[$k]['description'] = $p[$v['parent_id']]['description'];
                    $result[$k]['new'] = $p[$v['parent_id']]['new'];
                    $result[$k]['featured'] = $p[$v['parent_id']]['featured'];
                    $result[$k]['visible_individually'] = $p[$v['parent_id']]['visible_individually'];
                }
            }
            return $result;
        }else{
            return [];
        }
    }


    /**
     * 根据商品id获取默认显示的属性id
     *
     * @param $channel_info
     * @param $product_id
     * @return Model|\Illuminate\Database\Query\Builder|object|null
     */
    public static function getDefaultProductAttributeIdByProductId($channel_info,$product_id){
        $locale_product_id = Db::table('product_flat')->where([
            ['locale',$channel_info['locale_code']],
            ['channel',$channel_info['channel_code']],
            ['product_id',$product_id],
        ])->value('id');


        return Db::table('product_flat')->where([
            ['locale',$channel_info['locale_code']],
            ['channel',$channel_info['channel_code']],
            ['parent_id',$locale_product_id],
            ['status',1],
        ])->orderBy('price','ASC')->value('id');
    }

    /**
     * 根据上级id获取商品id
     * @param $parent_ids
     * @return array
     */
    public static function getProductIdByParentId($parent_ids){
        $result = Db::table('product_flat')->addSelect(['id','product_id'])
            ->whereIn('id',$parent_ids)->get();

        if(!empty($result)){
            $result = object_to_array($result);
            foreach($result as $k=>$v){
                 $res[$v['id']] = $v['product_id'];
            }
        }else{
            $res = [];
        }

        return $res;
    }


    /**
     * 根据商品id获取商品的其他信息
     *
     * @author 邹柯
     * @param $product_ids string 是 商品id列表
     * @param string $locale
     * @return array|\Illuminate\Support\Collection
     */
    private static function getGoodsByProductIds($product_ids,$channel_info){
        $result = Db::table('product_flat')->addSelect(['id','product_id','description','new','featured','visible_individually'])
            ->whereIn('product_id',$product_ids)
            ->where('locale','=',$channel_info['locale_code'])->get();

        if(!empty($result)){
            $result = object_to_array($result);
        }else{
            $result = [];
        }

        return $result;
    }

    /**
     * 根据商品属性id获取商品属性信息
     *
     * @author 邹柯
     * @param $product_attribute_ids
     * @return array|\Illuminate\Support\Collection
     */
    public static function getGoodsAttributes($product_attribute_ids){
        $result = Db::table('product_flat')->addSelect(['id','name','price','status','parent_id','channel'])
            ->whereIn('id',$product_attribute_ids)->get();

        if(!empty($result)){
            $result = object_to_array($result);
        }else{
            $result = [];
        }
        return $result;
    }

    /**
     * 根据商品id获取商品属性
     *
     * @author 邹柯
     * @param $product_id int 是 商品id
     * @param $locale string 是 本地化
     * @return mixed
     */
    public static function getGoodsAttributesByProductId($product_id,$product_attribute_id = null,$locale = 'zh-cn'){
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

        }else{
            return [];
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
     * 根据商品id获取商品图片
     *
     * @author 邹柯
     * @param $product_ids array 是 商品id
     * @return mixed
     */
    public static function getGoodsImageByProductIds($product_ids){
        $result = Db::table('product_images')->addSelect(['product_id',DB::raw('group_concat(path) as image_paths')])
            ->whereIn('product_id',$product_ids)
            ->groupBy('product_id')
            ->get();

        if(!empty($result)){
            $result = object_to_array($result);
            foreach ($result as $k => $v) {
                $p_imgs[$v['product_id']] = $v['image_paths'];
            }
            return $p_imgs;
        }else{
            return [];
        }
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
        }else{
            return [];
        }

        return $result;
    }


    /**
     * 获取商品收藏列表
     *
     * @author 邹柯
     * @param $seller_id int 是 店铺id
     * @param $customer_id int 是 客户id
     * @param $page int 是 页码
     * @param $page_size int 是 每页显示条数
     * @param $locale string 是 本地化
     * @return array|\Illuminate\Support\Collection
     */
    public static function getGoodsCollection($seller_id,$customer_id,$page,$page_size,$channel_info){
        $offset = ($page - 1) * $page_size;

        $count = Db::table('product_collection')
            ->where([
                ['seller_id','=',$seller_id],
                ['customer_id','=',$customer_id],
            ])->count();

        $total_page_sizes = ceil($count/$page_size);

        $result = Db::table('product_collection as pc')->addSelect(['id','created_at','product_id','product_attribute_id'])
            ->where([
                ['pc.seller_id','=',$seller_id],
                ['pc.customer_id','=',$customer_id]
            ])->offset($offset)->limit($page_size)->get();
        if(!empty($result)){
            $result = object_to_array($result);
            $product_ids = array_unique(array_column($result,'product_id'));
            $product_attribute_ids = array_unique(array_column($result,'product_attribute_id'));
            $product_detail = self::getGoodsDetail($product_ids,$product_attribute_ids,$channel_info);
            if(!empty($product_detail)){
                foreach($product_detail as $k=>$v){
                    $p_detail[$v['id']]['name'] = $v['name'];
                    $p_detail[$v['id']]['price'] = $v['price'];
                    $p_detail[$v['id']]['image_paths'] = $v['image_paths'];
                    $p_detail[$v['id']]['status'] = $v['status'];
                }
            }
            foreach($result as $k=>$v){
                $image_paths = isset($p_detail[$v['product_attribute_id']]['image_paths'])? $p_detail[$v['product_attribute_id']]['image_paths'] : null;
                if(!empty($image_paths)){
                    $image_path = explode(",",$image_paths)[0];
                }else{
                    $image_path = null;
                }
                $result[$k]['image_path'] = $image_path;
                $result[$k]['name'] = isset($p_detail[$v['product_attribute_id']]['name'])?$p_detail[$v['product_attribute_id']]['name'] : null;
                $result[$k]['price'] = isset($p_detail[$v['product_attribute_id']]['price'])? $p_detail[$v['product_attribute_id']]['price'] : null;
                $result[$k]['status'] = isset($p_detail[$v['product_attribute_id']]['status'])? $p_detail[$v['product_attribute_id']]['status'] : null;
            }
        }else{
            $result = [];
        }


        return ['page'=>$page,'page_size'=>$page_size,'total_page_sizes'=>$total_page_sizes,'result'=>$result];
    }

    /**
     * 取消商品收藏
     *
     * @author 邹柯
     * @param $product_collection_id int 是 商品收藏id
     * @return int
     */
    public static function cancelGoodsCollection($product_collection_id){
        return DB::table('product_collection')->where('id',$product_collection_id)->delete();
    }

    /**
     * 收藏商品
     *
     * @author 邹柯
     * @param $seller_id int 是 店铺id
     * @param $customer_id int 是 客户id
     * @param $product_id int 是 商品id
     * @param $product_attribute_id int 是 商品属性id
     * @return bool
     */
    public static function addGoodsCollection($seller_id,$customer_id,$product_id,$product_attribute_id){
        return DB::table('product_collection')->insert([
            'seller_id'=>$seller_id,
            'customer_id'=>$customer_id,
            'product_attribute_id'=>$product_attribute_id,
            'product_id'=>$product_id,
            'created_at'=>date("Y-m-d H:i:s")
        ]);
    }
}
