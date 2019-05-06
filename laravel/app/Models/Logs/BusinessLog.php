<?php

namespace App\Models\Logs;

use App\Models\BaseModel;
use Illuminate\Support\Facades\DB;

/**
 * Created by sublime3.
 * Index: zhangdahao
 * Date: 2018/12/19
 * Time: 15:31
 */
class BusinessLog extends BaseModel
{
    protected $table = 't_business_log';
    public $timestamps = false;

    protected $logType = '';
    /**
     * 记录操作日志
     *
     * @param string $createdUserName 创建人
     *        string $logMsg 修改信息
     *
     * @param $logMsg
     * @param $logTag
     * @param null $relatedId
     * @return bool
     */
    public function saveLog($createdUserName, $logMsg, $logTag, $relatedId = null)
    {
        if($this->logType == '' || $createdUserName == '' || $createdUserName == null)
            $this->logType = get_called_class();

        $data = [
            'relatedTableName'=> $this->table,
            'createdBy'=>$createdUserName,
            'createdOn'=>time(),
            'logMessage'=>$logMsg,
            'relatedid'=> is_numeric($this->getKey()) ? $this->getKey() : ($relatedId == null ? 0 : $relatedId),
            'type' => $this->logType,
            'tag' => $logTag
        ];

        $log = new BusinessLog();

        return $log->insert($data);
    }

    public static function getLogsStatic($relatedTable, $relatedId, $logTag = '')
    {
        $whereCl = [];
        if($logTag != ''){
            $whereCl[] = ['tag', '=', $logTag];
        }
        $whereCl[] = ['relatedTableName', '=', $relatedTable];
        $whereCl[] = ['relatedid', '=', $relatedId];

        return self::where($whereCl)->get();
    }
}