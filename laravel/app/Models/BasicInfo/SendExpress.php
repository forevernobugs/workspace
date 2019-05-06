<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/4/19
 * Time: 10:07
 */

namespace App\Models\BasicInfo;

use App\Models\BaseModel;

use Illuminate\Support\Facades\DB;

class SendExpress extends BaseModel
{
    protected $table = 't_warehouse_info';

    /**
     * 获取快递单号等相关信息
     */
    public function getSendExpressList($params = [])
    {
        //快递信息列表
        $res = DB::table('t_send_express_detail')
            ->select('id',
                'related_no',
                'parent_no',
                'child_no',
                'box_number',
                'status',
                'is_send_off',
                'print_time'
            );

        //是否打印条件
        if (isset($params['status']) && !empty($params['status'])) {
            $res->where('status',$params['status'] == 1 ? $params['status'] : 0);
        }
        //是否寄出条件
        if (isset($params['is_send_off']) && !empty($params['is_send_off'])) {
            $res->where('is_send_off',$params['is_send_off'] == 1 ? $params['is_send_off'] : 0);
        }

        //搜索条件
        $condition = [];
        $condition[] = $this->buildPara($params, 'related_no', 'like');
        $condition[] = $this->buildPara($params, 'parent_no', 'like');
        $condition[] = $this->buildPara($params, 'child_no', 'like');
        $condition[] = $this->buildPara($params, 'box_number', 'like');

        return $this->getList($res, $condition, $params);
    }


}