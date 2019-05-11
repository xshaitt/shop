<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductCartTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_cart', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('product_id')->unsigned()->comment('商品id');
            $table->integer('product_attribute_id')->unsigned()->comment('商品属性id');
            $table->integer('count')->unsigned()->comment('商品数量');
            $table->integer('customer_id')->unsigned()->comment('客户id');
            $table->integer('seller_id')->unsigned()->comment('店铺id');
            $table->dateTime('created_at')->comment('添加时间');
        });

        DB::statement("ALTER TABLE `product_cart` comment '购物车'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('product_cart');
    }
}
