<?php
/**
 * Created by PhpStorm.
 * User: xiangbohua
 * Date: 2018/5/6
 * Time: 17:35
 */

namespace App\Models\BasicInfo;

use App\Models\BaseModel;
use DB;

class TransportCrop extends BaseModel {
	protected $table = 't_transport_provider';

	public $timestamps = false;

	/**
	 * 获取所有承运公司
	 */
	public function getTransportAll() {
		return DB::table('t_transport_provider')->groupBy('provider_name')->select('provider_name', 'id')->get()->toArray();
	}

	/**
	 * 获取司机列表
	 */
	public function driverList($params = [], $role) {
		$subOrgId = $this->getSubOrgId($params['user_id'], 't_user');
		$conditions = $this->getDriverConditions($params);

		$result = DB::table('t_user' . ' as tu')
			->select('tu.id', 'tu.username', 'tu.mobile', 'tu.create_time',
				'to.ogname', 'tu.enabled', 'tr.role_name')
			->leftjoin('t_user_role as tur', 'tur.user_id', '=', 'tu.id')
			->leftjoin('t_role as tr', 'tr.id', '=', 'tur.role_id')
			->leftjoin('t_organization as to', 'to.id', '=', 'tu.org_id')
			->whereIn('tr.role_code', $role)
			->whereIn('tu.org_id', $subOrgId)
			->where($conditions);

		$list = $this->getList($result, $conditions, $params);
		$list['subOrgs'] = $this->getSubOrgs($params['user_id'], 't_user');

		return $list;
	}

	/**
	 * 司机存储
	 */
	public function driver_save($params = []) {
		$where = [['role_id', '=', $params['roleid']], ['user_id', '=', $params['userid']]];
		$exist = DB::table('t_user_role')->where($where)->exists();
		if ($exist) {return false;}

		$data = [
			'user_id' => $params['userid'],
			'role_id' => $params['roleid'],
		];

		return DB::table('t_user_role')->insert($data);
	}

	/**
	 * 构造司机筛选条件
	 */
	protected function getDriverConditions($params = []) {
		$conditions = [];
		if (isset($params['username']) && !empty($params['username'])) {
			$conditions[] = ['tu.username', 'like', '%' . $params['username'] . '%'];
		}

		if (isset($params['mobile']) && !empty($params['mobile'])) {
			$conditions[] = ['tu.mobile', 'like', '%' . $params['mobile'] . '%'];
		}

		if (isset($params['org']) && !empty($params['org'])) {
			$conditions[] = ['tu.org_id', '=', $params['org']];
		}

		return $conditions;
	}

	/**
	 * 获取供应商列表
	 */
	public function supplierList($params = []) {
		$condition[] = $this->buildPara($params, 'provider_name', 'like');
		$condition[] = $this->buildPara($params, 'provider_desc', 'like');
		$condition[] = $this->buildPara($params, 'provider_code', 'like');
		$condition[] = $this->buildPara($params, 'kdn_code', 'like');
		$condition[] = $this->buildPara($params, 'address', 'like');
		$condition[] = $this->buildPara($params, 'contact', 'like');
		$condition[] = $this->buildPara($params, 'contact_tel', 'like');

		$result = DB::table($this->table)->select('*');
		$this->setWhereBetween($result, $params, 'max_weight', 'max_weight_start', 'max_weight_end');

		return $this->getList($result, $condition, $params);
	}

	public function get_one($params = []) {
		if (!isset($params['id']) || true === empty($params['id']) || !is_numeric($params['id'])) {
			return '未传入id';
		}

		return $this->where('id', $params['id'])->first()->toArray();
	}

	/**
	 * 修改数据
	 *
	 */
	public function do_update($params = []) {
		if (!isset($params['id']) || !is_numeric($params['id'])) {
			return '未传入id';
		}
		$data = $this->_filterField($params);
		return $this->where('id', $params['id'])->update($data);
	}

	/**
	 * 存储数据
	 *
	 */
	public function do_save($params = []) {
		$data = $this->_filterField($params);
		return $this->insert($data);
	}

	/**
	 * 构造数据
	 *
	 */
	protected function _filterField($params = []) {
		$data = $this->filterField($params, [
			'provider_name', 'provider_desc', 'provider_code',
			'kdn_code', 'contact', 'contact_tel',
			'max_weight', 'address',
		]);
		$data['enabled'] = (isset($params['enabled']) && $params['enabled'] >= 1) || false === empty($params['enabled']) ? 1 : 0;

		return $data;
	}

	/**
	 * 获取总仓统计信息
	 */
	public function getStatisticalInfo($params = []) {
		$condition = [];
		if (isset($params['from_warehouse']) && !empty($params['from_warehouse'])) {
			$condition[] = ['wifrom.warehouse_name', 'like', '%' . $params['from_warehouse'] . '%'];
		}
		if (isset($params['to_warehouse']) && !empty($params['to_warehouse'])) {
			$condition[] = ['wito.warehouse_name', 'like', '%' . $params['to_warehouse'] . '%'];
		}

		$model = DB::table('t_goods_collection as gc')
			->leftJoin(
				't_goods_collection_detail as gci',
				'gci.order_no',
				'=',
				'gc.order_no'
			)->join(
			't_warehouse_info as wifrom',
			'wifrom.warehouse_code',
			'=',
			'gc.warehouse_code'
		)->join(
			't_warehouse_info as wito',
			'wito.warehouse_code',
			'=',
			'gc.destination_code'
		)->leftJoin(
				't_dispense_order_detail as dod',
				'dod.goods_no',
				'=',
				'gci.goods_code'
		)->leftJoin('t_dispense_order as tdo', function ($join) {
        	$join->on('tdo.order_number', '=', 'dod.order_number')
        		 ->where('tdo.order_status', '>', 1);
        })->select(
				'wifrom.warehouse_name as from_warehouse',
				'wifrom.warehouse_code as from_warehouse_code',
				'wito.warehouse_name as to_warehouse',
				'wito.warehouse_code as to_warehouse_code',
				DB::raw('COUNT(DISTINCT gc.order_no) as order_number'), //集货单数
				DB::raw('COUNT(DISTINCT gci.id) as box_number'), //集货箱数
				DB::raw('COUNT(DISTINCT dod.goods_no) as sent_number'), //发出箱数
				DB::raw('LEFT(gc.create_time,10) as collection_time')
			)->orderBy('gc.create_time', 'desc')
			->groupBy(
				DB::raw('LEFT(gc.create_time,10)'),
				'gc.warehouse_code',
				'gc.destination_code'
			);

		$this->setWhereBetween($model, $params, 'gc.create_time', 'create_time_s', 'create_time_e');
		return $this->getList($model, $condition, $params);
	}

	/**
	 * 获取出库箱数
	 */
	public function getOutBoxNumber($info = []) {
		$startTime = date('Y-m-d H:i:s', strtotime($info['collection_time']) - 6 * 60 * 60);
		$endTime = date('Y-m-d H:i:s', strtotime($info['collection_time']) + 18 * 60 * 60 - 1);

		return DB::table('t_order_box')
			->select(DB::raw('COUNT(DISTINCT box_number) as out_box_number'))
			->where('destination_code', $info['to_warehouse_code'])
			->whereBetween('create_time', [$startTime, $endTime])
			->get()[0]->out_box_number;
	}
}