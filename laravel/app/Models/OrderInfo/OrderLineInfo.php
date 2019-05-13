<?php

namespace App\Models\OrderInfo;

use App\Models\BaseModel;
use Illuminate\Support\Facades\DB;

/**
 * Created by PhpStorm.
 * User: xiangbohua
 * Date: 2018/5/10
 * Time: 15:37
 */

class OrderLineInfo extends BaseModel
{
    protected $table = 't_order_line_info';

    public $timestamps = false;

    public static function getLineCode($line, $dealerId)
    {
        $lineInfo = DB::select("SELECT DISTINCT line_code FROM t_order_line_info
            WHERE SUBSTRING(line_code, 3) = $line AND dealer_id = $dealerId");

        return isset($lineInfo[0]) ? $lineInfo[0]->line_code : '';
    }
}