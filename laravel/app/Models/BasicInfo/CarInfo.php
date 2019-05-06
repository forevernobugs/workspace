<?php

namespace App\Models\BasicInfo;

use App\Models\BaseModel;
use Illuminate\Support\Facades\DB;

/**
 * Created by PhpStorm.
 * User: xiangbohua
 * Date: 2018/4/19
 * Time: 20:35
 */

class CarInfo extends BaseModel {
	protected $table = 't_car';

	/**
	 * 获取汽车列表
	 */
	public function carList($params = []) {
		$subOrgId = $this->getSubOrgId($params['user_id'], $this->table);
		$condition[] = $this->buildPara($params, 'tc.car_name', 'like');
		$condition[] = $this->buildPara($params, 'tc.plate_number', 'like');
		$condition[] = $this->buildPara($params, 'tc.driver_name', 'like');
		$condition[] = $this->buildPara($params, 'tc.driver_contact', 'like');
		$condition[] = $this->buildPara($params, 'ttp.provider_name', 'like');
		$condition[] = $this->buildPara($params, 'ttp.contact', 'like');
		$condition[] = $this->buildPara($params, 'ttp.contact_tel', 'like');
		$condition[] = $this->buildPara($params, 'cfi.reg_code', 'like');
		$condition[] = $this->buildPara($params, 'tc.org_id', '=');

		$result = DB::table($this->table . ' as tc')
			->select('tc.*', 'to.ogname', 'ttp.provider_name',
				'ttp.contact', 'ttp.contact_tel', 'cfi.reg_code')
			->leftJoin('t_organization as to', 'tc.org_id', 'to.id')
			->leftJoin('t_transport_provider as ttp', 'tc.crop_id', 'ttp.id')
			->leftJoin('t_consign_fencing_info as cfi', 'cfi.id', 'tc.fencing_id')
			->whereIn('tc.org_id', $subOrgId);

		$this->setWhereBetween($result, $params, 'tc.created', 'star', 'end');
		$this->setWhereBetween($result, $params, 'tc.max_weight', 'max_weight_start', 'max_weight_end');
		$this->setWhereBetween($result, $params, 'tc.max_volume', 'max_volume_start', 'max_volume_end');

		$list = $this->getList($result, $condition, $params);
		$list['subOrg'] = $this->getSubOrgs($params['user_id'], $this->table);

		return $list;
	}

	/**
	 * 获取所有可用汽车
	 */
	public function carListAll($params = []) {
		$subOrgId = $this->getSubOrgId($params['user_id'], $this->table);

		return DB::table($this->table . ' as tc')
			->select('tc.car_name', 'tc.id')
			->leftJoin('t_organization as to', 'tc.org_id', 'to.id')
			->whereIn('tc.org_id', $subOrgId)
			->get();
	}

	/**
	 * 获取单个汽车
	 */
	public function get_one($params = []) {
		if (!isset($params['id']) || true === empty($params['id'])) {
			return '未传入id';
		}

		return $this->where('id', $params['id'])->first();
	}

	/**
	 * 更新
	 */
	public function do_update($params = []) {
		if (!isset($params['id']) || !is_numeric($params['id'])) {
			return '未传入id';
		}
		$data = $this->_filterField($params);
		return $this->where('id', $params['id'])->update($data);
	}

	/**
	 * 存储
	 */
	public function do_save($params = []) {
		$data = $this->_filterField($params);
		$data['created'] = date("Y-m-d H:i:s");

		return $this->insert($data);
	}

	/**
	 * 构造参数
	 */
	protected function _filterField($params = []) {
		$data = $this->filterField($params, [
			'car_name', 'plate_number', 'max_weight',
			'max_volume', 'driver_name', 'driver_contact',
			'org_id', 'crop_id', 'remark', 'fencing_id',
		]);

		$data['in_using'] = (isset($params['in_using']) && $params['in_using'] >= 1) || false === empty($params['in_using']) ? 1 : 0;
		$data['modified'] = date("Y-m-d H:i:s");

		return $data;
	}
}