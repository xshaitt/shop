<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sellers extends Model
{
    protected $table = 'sellers';
    protected $fillable = ['name', 'group_id', 'admin_id'];
}
