<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Class Channel
 *
 * @author 邹柯
 * @package App\Models
 */
class Channel extends Model
{
    /**
     * 获取渠道信息
     *
     * @return array
     */
    public static function getChannel(){
        $result = (array)Db::table('channels as c')
            ->addSelect([
                'c.id',
                'c.code as channel_code',
                'c.name as channel_name',
                'c.description as channel_description',
                'c.timezone',
                'c.theme',
                'c.logo',
                'l.code as locale_code',
                'l.name as locale_name',
                'cu.code as currency_code',
                'cu.name as currency_name'])
            ->leftJoin('locales as l','c.default_locale_id','=','l.id')
            ->leftJoin('currencies as cu','c.base_currency_id','=','cu.id')
            ->where('c.code',config('app.channel'))->first();

        return $result;
    }
}
