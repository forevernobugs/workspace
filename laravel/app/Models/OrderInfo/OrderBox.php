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

class OrderBox extends BaseModel
{
    protected $table = 't_order_box';

    public $timestamps = false;


    //根据箱号获取所有商品
    public static function getProductQuantityByBoxNumber($box_number){
	    return DB::table('t_order_box_detail')
            ->select('out_order','box_number','quantity')
            ->whereIn('box_number', $box_number)
            ->get()->toArray();
    }

    //根据订单号获取箱号以及商品数
    public static function getBoxQuantityByOrder($order_number){
	    $model = DB::table('t_order_box_detail')
            ->select(
            	'box_number',
            	'order_number',
            	DB::raw('SUM(quantity) as quantity')
            );
        if (is_array($order_number)) {
        	$model->whereIn('order_number', $order_number);
     	} else {
     		$model->where('order_number', $order_number);
     	}
        return $model->groupBy('box_number')
            ->get()
            ->toArray();
    }

    //根据订单号获取获取箱号 key = product  value = box_number
    public static function getBoxByProduct($product_code){
        $model = DB::table('t_order_box_detail');
        if (is_array($product_code)) {
            $model->whereIn('product_code', $product_code);
        } else {
            $model->where('product_code', $product_code);
        }
        return $model->pluck('box_number', 'product_code')->toArray();
    }

    // 获取运货单中的所有商品以及箱号
    public static function getBoxQuantityByDelivery($delivery_no){
        $model = DB::table('t_order_box_detail as obd')
                ->select(
                    'obd.box_number',
                    'cocb.delivery_number as delivery_no',
                    DB::raw('SUM(obd.quantity) as quantity')
                )
                ->leftJoin(
                    't_consign_order_consign_box as cocb',
                    'cocb.box_number',
                    '=',
                    'obd.box_number'
                );
        if (is_array($delivery_no)) {
            $model->whereIn('cocb.delivery_number', $delivery_no);
        } else {
            $model->where('cocb.delivery_number', $delivery_no);
        }
        return $model->groupBy('box_number')->get()->toArray();
    }


    /**
     * 根据箱号获取订单号
     */
    public static function getOrderByBox($box_number){
        return DB::table('t_order_box')->select('order_number', 'sm_order')->where('box_number', $box_number)->first();
    }
    
    /** 
     * 到达分仓 
     */ 
    public static function arriveDranch($box_number){ 
        try { 
            DB::table('t_order_box')->where('box_number', $box_number)->update(['is_arrive'=>1]); 
        } catch (\Exception $e) { 
            throw $e; 
        } 
        return true; 
    } 
 
    /** 
     * 根据箱号获取已经到达的运货单 
     */ 
    public static function getArriveDelivery($box_number){ 
        $prefix = substr($box_number, 0, 4); 
 
        $order_number = DB::table('t_order_box')->where('box_number', $box_number)->value('order_number'); 
        // 省代箱号 
        if ($prefix == 'RBAP') { 
            $count = DB::table('t_order_box') 
                ->where('order_number', $order_number) 
                ->where('box_number', 'like', 'RBAP%') 
                ->where('is_arrive', 0) 
                ->count(); 
        } else { 
            $count = DB::table('t_order_box') 
                ->where('order_number', $order_number) 
                ->where('box_number', 'not like', 'RBAP%') 
                ->where('is_arrive', 0) 
                ->count(); 
        } 
 
        // 还有未到箱子 
        if ($count > 0) { 
            return false; 
        } 
 
        // 获取 
        if ($prefix == 'RBAP') { 
            $delivery_no = DB::table('t_delivery_order')->where('related_no', $order_number)->where('stock_dc_id', '<>', '455')->value('delivery_no'); 
        } else { 
            $delivery_no = DB::table('t_delivery_order')->where('related_no', $order_number)->where('stock_dc_id', '=', '455')->value('delivery_no'); 
        } 
 
        if (empty($delivery_no)) { 
            return false; 
        } 
 
        return $delivery_no; 
    }

}