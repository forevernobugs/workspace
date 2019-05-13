<?php
/**
 * Created by PhpStorm.
 * Index: xiangbohua
 * Date: 27/04/2017
 * Time: 11:19
 */

namespace App\Common;

use Illuminate\Support\Facades\DB;

class OrderNumber
{
    /**
     * 重要，请勿在事务内部调用此方法，本方法会开启新事务，将导致上一个事务直接提交
     * 请勿在事务内部调用此方法
     * 根据订单类型获取新的订单号
     * @param string $prefix 订单类型前缀
     * @return string 新订单号，订单前缀为空时返回空字符串
     */
    public static function getNextNumber($prefix)
    {
        if (empty($prefix)) {
            return '';
        }

        try {
            $datePrefix = date("Ymd");

            DB::beginTransaction();
            $orderNumber =  DB::table('t_order_number')->
                            where('order_type_prefix', $prefix)->
                            where(['order_date_prefix'=> $datePrefix])->
                            select('order_secquence_number', 'id')->
                            orderBy('id', 'desc')->first();

            $next = isset($orderNumber) || isset($orderNumber->order_secquence_number) ? $orderNumber->order_secquence_number + 1: 1;

            DB::table('t_order_number')->insert(
                ['order_type_prefix' => $prefix, 'order_date_prefix' => $datePrefix, 'order_secquence_number'=>$next]
            );

            DB::commit();
            return $prefix. $datePrefix. str_pad($next, 5, '0', STR_PAD_LEFT);
        } catch (\Exception $exception) {
            DB::rollBack();
            return '';
        }
    }

    /**
     * 获取随机的订单号，前缀+秒级时间戳+$nonceLength 位随机数
     * @param string $prefix  前缀
     * @param int $nonceLength 随机数字符串
     * @return string  订单号
     */
    public static function getRandomOrderNumber($prefix, $nonceLength = 5)
    {
        if (empty($nonceLength)) {
            $nonceLength = 5;
        }
        $randomNumber = Signature::nonce($nonceLength);
        $time = hdate(time(), 'YmdHis');
        return $prefix.$time.$randomNumber;
    }
}
