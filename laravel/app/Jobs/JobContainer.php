<?php
/**
 * Created by PhpStorm.
 * User: xiangbohua
 * Date: 14/09/2017
 * Time: 18:59
 */

namespace App\Jobs;


use App\Jobs\OrderJobs\TestJob;

class JobContainer
{
    //此处名称必须和类名一直，否则将无法找到接口
    const JOP_ALIAS = [
        //--WMS任务名称
        'out_order'=>TestJob::class,


    ];

    public static function getClassFullName($shortName){
        $shortName = $shortName == null? '': strtolower($shortName);
        if(!key_exists($shortName, self::JOP_ALIAS)){
            return '';
        }
        return self::JOP_ALIAS[$shortName];
    }
}