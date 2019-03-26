<?php

namespace Webkul\Core\Database\Seeders;

use Illuminate\Database\Seeder;
use DB;

class LocalesTableSeeder extends Seeder
{
    public function run()
    {
        DB::table('channels')->delete();

        DB::table('locales')->delete();

        DB::table('locales')->insert([
            'id' => 1,
            'code' => 'en',
            'name' => 'English',
        ]);

        DB::table('locales')->insert([
            'id' => 2,
            'code' => 'zh-cn',
            'name' => '简体中文',
        ]);
    }
}