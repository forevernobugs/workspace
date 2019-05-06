<?php
/**
 * Created by PhpStorm.
 * Index: xiangbohua
 * Date: 07/09/2017
 * Time: 18:27
 */

namespace App\Common;

class Signature
{
    public static function generate(array $para)
    {
        $dic = [];
        $dic[] = $para['key'].'';
        $dic[] = $para['secret'].'';
        $dic[] = $para['timestamp'].'';
        $dic[] = $para['nonce'].'';

        sort($dic, SORT_STRING);
        return md5($dic[0].$dic[1].$dic[2].$dic[3]);
    }

    public static function nonce($length = 6)
    {
        $arr = array();
        while (count($arr)<$length) {
            $arr[]=rand(0, 9);
            $arr=array_unique($arr);
        }
        return implode('', $arr);
    }
}
