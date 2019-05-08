<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class CreateProductCollectionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_collection', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('product_id')->unsigned()->comment('商品id');
            $table->integer('customer_id')->unsigned()->comment('客户id');
            $table->integer('product_attribute_id')->unsigned()->comment('商品属性id');
            $table->integer('seller_id')->unsigned()->comment('店铺id');
            $table->dateTime('created_at')->comment('收藏时间');
        });
        DB::statement("ALTER TABLE `product_collection` comment '商品收藏'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('product_collection');
    }
}
