<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Created by PhpStorm.
 * Index: xiangbohua
 * Date: 2018/4/19
 * Time: 11:51
 */
abstract class BaseModel extends Model {
	public $timestamps = false;

	// 默认页容
	const PAGESIZE = 20;

	/**
	 * 过滤冗余字段 并重组
	 * @param $table string 待过滤的表名
	 * @param $data array 过滤数据
	 * @return array 
	 */
	public function filterRegroup($table, $data){
		$table_schema = env('DB_DATABASE', '');
    	$field = DB::table('information_schema.COLUMNS')->where([
    		['table_name', '=', $table],
    		['table_schema', '=', $table_schema],
    	])->pluck('COLUMN_NAME')
    	->toArray();
    	
    	if (empty($field) || empty($data)) {
    		return false;
    	}

    	foreach ($data as $field_redundancy => $item) {
    		if (!in_array($field_redundancy, $field)) {
    			unset($data[$field_redundancy]);
    		}
    	}
    	return $data;
	}

	/**
	 * 绑定where条件
	 * like 使用全模糊查 如需要半迷糊请使用原先的方式
	 * @param $params array 搜索条件
	 * @param $keyName string 索引名
	 * @param $operator string 操作条件
	 */
	protected function buildPara(array $params, string $keyName, string $operator) {
		$arr = explode('.', $keyName);
		$keyValue = end($arr);
		if (isset($params[$keyValue]) && !empty($params[$keyValue])) {
			if ($operator == 'like') {
				return [$keyName, $operator, "%{$params[$keyValue]}%"];
			}
			return [$keyName, $operator, $params[$keyValue]];
		}
		return [];
	}

	/**
	 * 获取一个列表
	 * @param $searchModel object 查询对象
	 * @param $where array 限制条件
	 * @param $requestAll array 请求参数 主要验证是否是导出文件
	 * @return 结果集
	 */
	protected function getList($searchModel, array $where, array $requestAll) {
		//过滤空条件
		foreach ($where as $key => $value) {
			if (empty($value)) {
				unset($where[$key]);
			}
		}

		//判断是否是导出文件
		if (isset($requestAll['request_file'])) {
			$result = json_decode($searchModel->where($where)->get()->toJson(), true);
			return ['list' => $result];
		}
		// hOpenDBLog();
		$result = json_decode($searchModel->where($where)->paginate($requestAll['pagesize'])->toJson(), true);
		// dd(hGetDBLogStr());
		$data = $result['data'];
		unset($result['data']);
		return ['list' => $data, 'page' => $result];
	}

	/**
	 * 批量更新，支持传入主键、单一字段名称
	 * [
	 *  ['id'=>id1, '更新字段'=>新value],
	 *  ['id'=>id2, '更新字段'=>新value],
	 *  ['id'=>id3, '更新字段'=>新value]
	 * ]
	 * @param $multipleData
	 * @return bool|int
	 */
	public function updateBatch($multipleData) {
		$tableName = DB::getTablePrefix() . $this->getTable(); // 表名
		return self::updateBatchStatic($tableName, $multipleData);
	}

	/**
	 * 批量更新，支持传入主键、单一字段名称
	 * [
	 *  ['id'=>id1, '更新字段'=>新value],
	 *  ['id'=>id2, '更新字段'=>新value],
	 *  ['id'=>id3, '更新字段'=>新value]
	 * ]
	 * @param string $tableName 需要更新的表名
	 * @param array $multipleData
	 * @return bool|int
	 */
	public static function updateBatchStatic($tableName, $multipleData) {
		try {
			if (empty($multipleData)) {
				throw new \Exception("数据不能为空");
			}
			$firstRow = current($multipleData);

			$updateColumn = array_keys($firstRow);
			// 默认以id为条件更新，如果没有ID则以第一个字段为条件
			$referenceColumn = isset($firstRow['id']) ? 'id' : current($updateColumn);
			unset($updateColumn[0]);
			// 拼接sql语句
			$updateSql = "UPDATE " . $tableName . " SET ";
			$sets = [];
			$bindings = [];
			foreach ($updateColumn as $uColumn) {
				$setSql = "`" . $uColumn . "` = CASE ";
				foreach ($multipleData as $data) {
					$setSql .= "WHEN `" . $referenceColumn . "` = ? THEN ? ";
					$bindings[] = $data[$referenceColumn];
					$bindings[] = $data[$uColumn];
				}
				$setSql .= "ELSE `" . $uColumn . "` END ";
				$sets[] = $setSql;
			}
			$updateSql .= implode(', ', $sets);
			$whereIn = collect($multipleData)->pluck($referenceColumn)->values()->all();
			$bindings = array_merge($bindings, $whereIn);
			$whereIn = rtrim(str_repeat('?,', count($whereIn)), ',');
			$updateSql = rtrim($updateSql, ", ") . " WHERE `" . $referenceColumn . "` IN (" . $whereIn . ")";
			// 传入预处理sql语句和对应绑定数据
			return DB::update($updateSql, $bindings);
		} catch (\Exception $e) {
			hFormatException($e);
			return false;
		}
	}

	/**
	 * 尝试从数组中获取pagesize的字段
	 * @param $para
	 * @param $default
	 * @return mixed
	 */
	protected function tryGetPageSize($para, $default) {
		if (isset($para['pagesize']) && is_numeric($para['pagesize']) && $para['pagesize'] > 0) {
			return $para['pagesize'];
		}

		return $default;
	}

	/**
	 * 设置like
	 * 仅在查询是使用，帮助设置查询条件
	 * @param $whereModel
	 * @param $para
	 * @param $whereFiled
	 * @param bool $isOr
	 */
	protected function setWhereLike($whereModel, $para, $whereFiled, $isOr = false) {
		$whereKey = $whereFiled;
		if (strpos($whereFiled, '.')) {
			$whereKey = explode('.', $whereFiled)[1];
		}

		if (isset($para[$whereKey])) {
			if ($isOr) {
				$whereModel->orWhere($whereFiled, 'LIKE', "%{$para[$whereKey]}%");
			} else {
				$whereModel->where($whereFiled, 'LIKE', "%{$para[$whereKey]}%");
			}
		}
	}

	/**
	 * 设置=
	 * 仅在查询是使用，帮助设置查询条件
	 * @param $whereModel
	 * @param $para
	 * @param $whereFiled
	 * @param bool $isOr
	 */
	protected function setWhereEq($whereModel, $para, $whereFiled, $isOr = false) {
		$whereKey = $whereFiled;
		if (strpos($whereFiled, '.')) {
			$whereKey = explode('.', $whereFiled)[1];
		}
		if (isset($para[$whereFiled])) {
			if ($isOr) {
				$whereModel->orWhere($whereFiled, '=', $para[$whereKey]);
			} else {
				$whereModel->where($whereFiled, '=', $para[$whereKey]);
			}
		}
	}

	/**
	 * 设置 <>
	 * 仅在查询是使用，帮助设置查询条件
	 * @param $whereModel
	 * @param $para
	 * @param $whereFiled
	 * @param bool $isOr
	 */
	protected function setWhereNotEq($whereModel, $para, $whereFiled, $isOr = false) {
		$whereKey = $whereFiled;
		if (strpos($whereFiled, '.')) {
			$whereKey = explode('.', $whereFiled)[1];
		}
		if (isset($para[$whereKey])) {
			if ($isOr) {
				$whereModel->orWhere($whereFiled, '<>', $para[$whereKey]);
			} else {
				$whereModel->where($whereFiled, '<>', $para[$whereKey]);
			}
		}
	}

	/**
	 * 设置 between 当参数传递不全时，自动改为 > 或者 <
	 * 仅在查询是使用，帮助设置查询条件
	 * @param $whereModel
	 * @param $para
	 * @param $whereFiled
	 */
	public function setWhereBetween($whereModel, $para, $whereFiled, $startFiled, $endFiled) {
		if (isset($para[$startFiled]) && isset($para[$endFiled])) {
			$whereModel->whereBetween($whereFiled, [$para[$startFiled], $para[$endFiled]]);
			return;
		}

		if (isset($para[$startFiled])) {
			$whereModel->where($whereFiled, '>=', $para[$startFiled]);
			return;
		}

		if (isset($para[$endFiled])) {
			$whereModel->where($whereFiled, '<=', $para[$endFiled]);
			return;
		}
	}

	/**
	 * 获取当前用户下级及本级组织id
	 * @param $userId 用户id
	 * @param $baseTable 基于哪张表获取下层组织
	 * return array
	 */
	public function getSubOrgId($userId, $baseTable) {
		$myOrgId = DB::table('t_user')->where('id', $userId)->value('org_id');
		$orgs = DB::select("SELECT SUBSTRING(tto.org_path, INSTR(tto.org_path,$myOrgId)) as orgs
            from t_organization as tto LEFT JOIN $baseTable as tu on tu.org_id = tto.id");

		$orgarr = [];
		foreach ($orgs as $value) {
			if ($value->orgs) {
				foreach (explode(',', $value->orgs) as $v) {
					array_push($orgarr, $v);
				}
			}
		}

		return array_unique($orgarr);
	}

	/**
	 * 获取当前用户下级及本级组织
	 * @param $userId 用户id
	 * @param $baseTable 基于哪张表获取下层组织
	 * return 带组织名的组织
	 */
	public function getSubOrgs($userId, $baseTable) {
		$subOrgId = $this->getSubOrgId($userId, $baseTable);

		return DB::table('t_organization as to')
			->select('to.id', 'to.ogname')
			->whereIn('to.id', $subOrgId)
			->get();
	}

	/**
	 * 尝试过滤参数
	 * @param $params
	 * @param $exceptedPara
	 * @return array
	 * @throws \App\Exceptions\KnownLogicException
	 */
	protected function filterField($params, $exceptedPara) {
		$data = [];
		checkLogic(!is_string($exceptedPara) || !is_array($exceptedPara), '参数不正确');
		$tempExcepted = $exceptedPara;
		if (is_string($exceptedPara)) {
			$tempExcepted = [$exceptedPara];
		}

		foreach ($tempExcepted as $exceptedKey) {
			if (isset($params[$exceptedKey]) && false === empty($params[$exceptedKey])) {
				$data[$exceptedKey] = $params[$exceptedKey];
			}
		}
		return $data;
	}

	/**
	 * 获取集货号
	 * @param $order_number 订单号
	 * @return bind_number
	 */
	public static function getBindNumber($orderNumber, $dealerId){
		// 判断是否需要创建集货号
        $order_secquence = DB::table('t_date_dealer_secquence_number')
                    ->where('order_number', $orderNumber)
                    ->orderBy('id', 'desc')
                    ->value('order_secquence_number');
        $order_secquence_number = $order_secquence;
        if (empty($order_secquence)) {
            //获取序列号
            $order_secquence_number = DB::table('t_date_dealer_secquence_number')
                        ->where('dete_dealer_prefix', date('ymd').$dealerId)
                        ->orderBy('id', 'desc')
                        ->value('order_secquence_number');
            if (empty($order_secquence_number)) {
                $order_secquence_number = 0;
            }
            $order_secquence_number++;

            // 添加序列号
            DB::beginTransaction();
            try {
                DB::table('t_date_dealer_secquence_number')
                    ->insert([
                        'dete_dealer_prefix'      =>  date('ymd').$dealerId,
                        'order_number'            =>  $orderNumber,
                        'order_secquence_number'  =>  $order_secquence_number,
                    ]);
                DB::commit();
            } catch (\Exception $ex) {
                DB::rollback();
                throw $ex;
            }
        }

        return  $bind_number = date('ymd').$dealerId.'-'.$order_secquence_number;
	}
}
