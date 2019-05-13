<?php
/**
 * Created by PhpStorm.
 * User: xiangbohua
 * Date: 10/05/2017
 * Time: 14:39
 */

namespace App\Models\Process;

use App\Models\BaseModel;
use Illuminate\Support\Facades\DB;

class ProcessTrace extends BaseModel
{
    protected $table = 't_process_trace';
    public $timestamps = true;
    const CREATED_AT = 'createdDate';
    const UPDATED_AT  = 'modifiedDate';


    public function getTraceForRequest($para)
    {
        if (isset($para['request_id'])) {
            $trace = DB::table($this->table.' as tra')
                ->leftJoin('erp_user as usrr', 'usrr.login_name', '=', 'tra.createdUser')
                ->where('requestId', $para['request_id'])
                ->select('message', 'createdUser', 'createdDate', 'usrr.username')
                ->orderBy('tra.createdDate')
                ->get();

            return object_to_array($trace);
        } else {
            return [];
        }
    }
}
