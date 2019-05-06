<?php

namespace App\Models\System;

use App\Models\BaseModel;
use Illuminate\Support\Facades\DB;

/**
 * Created by PhpStorm.
 * Index: xiangbohua
 * Date: 2018/4/19
 * Time: 15:54
 */
class Configuration extends BaseModel
{
    protected $table = 't_configuration';

    public static function getConfig($keyName)
    {
        return DB::table('t_configuration')->where('config_key', $keyName)->value('config_value');
    }
}