<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Class Index
 *
 * @author 邹柯
 * @package App\Models
 */
class Index extends model
{
    /**
     * 获取首页轮播图信息
     *
     * @return array|null
     */
    public static function getSliders($channel_info){
        $result = DB::table('sliders')->addSelect(['title','path','content'])->where('channel_id',$channel_info['id'])->get();
        if(!empty($result)){
            $result = object_to_array($result);
        }else{
            $result = null;
        }

        return $result;
    }
}