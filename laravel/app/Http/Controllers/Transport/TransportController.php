<?php

namespace App\Http\Controllers\Transport;

use App\Http\Controllers\LoginRequireController;
use App\Models\BasicInfo\CarInfo;
use App\Models\BasicInfo\ConsignFencing;
use App\Models\BasicInfo\TransportCrop;
use App\User;

class TransportController extends LoginRequireController {
	/**
	 * 获取汽车列表页
	 */
	public function carList(CarInfo $model) {
		$list = $model->carList($this->input);
		foreach ($list['list'] as &$car) {
			$car['max'] = '载重(t):' . $car['max_weight']
				. '<br/>容积(m³):' . $car['max_volume'];
			$car['driver'] = '姓名:' . $car['driver_name']
				. '<br/>Tel:' . $car['driver_contact'];
			$car['provider'] = '公司:' . $car['provider_name']
				. '<br/>联系人:' . $car['contact']
				. '<br/>Tel:' . $car['contact_tel'];
			$car['in_using'] = $car['in_using'] == 1 ? '是' : '否';
		}

		$list['title'] = [
			'id' => '车辆id',
			'car_name' => '车辆名称',
			'plate_number' => '车牌号',
			'max' => '载重/容积',
			'driver' => '司机',
			'provider' => '物流公司',
			'ogname' => '对应仓库',
			'reg_code' => '关联线路',
			'created' => '创建时间',
			'in_using' => '启用',
			'remark' => '备注',
		];

		return $this->returnList('加载成功', $list, $list['title'], '车辆信息');
	}

	/**
	 * 汽车编辑页
	 */
	public function carEdit(CarInfo $model) {
		$data = $model->get_one($this->input);
		if (empty($data)) {
			return hError('找不到车辆信息');
		}
		$subOrg = $model->getSubOrgs($this->input['user_id'], 't_car');

		$trans = new TransportCrop();
		$provider = $trans->getTransportAll();

		$fencing = new ConsignFencing();
		$fencings = $fencing->getFencingAll();

		return hSucceed('操作成功', [
			'item' => $data,
			'subOrg' => $subOrg,
			'provider' => $provider,
			'fencings' => $fencings,
		]);
	}

	/**
	 * 汽车编辑
	 */
	public function carUpdate(CarInfo $model) {
		$result = $model->do_update($this->input);

		if ($result) {
			return hSucceed('操作成功');
		}
		return hError('操作失败');
	}

	/**
	 * 汽车存储
	 */
	public function carSave(CarInfo $model) {
		$result = $model->do_save($this->input);

		if ($result) {
			return hSucceed('操作成功');
		}
		return hError('操作失败');
	}

	/**
	 * 获取司机列表页
	 */
	public function driverList(TransportCrop $model) {
		$list = $model->driverList($this->input, ['TRANPOSRT_DRIVER', 'DELIVERY_DRIVER']);

		$title = [
			'id' => '用户ID',
			'username' => '用户名',
			'mobile' => '手机',
			'role_name' => '司机类型',
			'ogname' => '对应仓库',
		];

		return $this->returnList('加载成功', $list, $title, '司机信息');
	}

	/**
	 * 司机编辑
	 */
	public function driverEdit(User $user) {
		$data = $user->geuUserlist($this->input);

		$title = [
			'login_name' => '登录名',
			'username' => '用户名',
			'mobile' => '手机号',
			'email' => '邮箱地址',
		];

		return hSucceed('加载成功', $data);
	}

	/**
	 * 司机存储
	 */
	public function driverSave(TransportCrop $model) {
		if (!isset($this->input['userid']) || true === empty($this->input['userid'])) {
			return hError('请选择用户');
		}

		$result = $model->driver_save($this->input);
		if ($result) {
			return hSucceed('操作成功');
		}
		return hError('该用户已是司机');
	}

	/**
	 * 获取供应商列表页
	 */
	public function supplierList(TransportCrop $model) {
		$list = $model->supplierList($this->input);

		$title = [
			'id' => '物流公司ID',
			'provider_name' => '物流公司名称',
			'provider_desc' => '物流公司描述',
			'provider_code' => '物流供应商编码',
			'kdn_code' => '快递编码',
			'contact' => '常用联系人',
			'contact_tel' => '常用联系人电话',
			'max_weight' => '最大配送重量(kg)',
			'address' => '地址',
			'enabled' => '是否启用',
		];

		return $this->returnList('加载成功', $list, $title, '供应商信息');
	}

	/**
	 * 物流供应商编辑
	 */
	public function supplierEdit(TransportCrop $model) {
		$data = $model->get_one($this->input);

		return hSucceed('操作成功', ['item' => $data]);
	}

	/**
	 * 物流供应商更新
	 */
	public function supplierUpdate(TransportCrop $model) {
		$result = $model->do_update($this->input);

		if ($result) {
			return hSucceed('操作成功');
		}
		return hError('操作失败');
	}

	/**
	 * 物流供应商新增
	 */
	public function supplierSave(TransportCrop $model) {
		$result = $model->do_save($this->input);

		if ($result) {
			return hSucceed('操作成功');
		}
		return hError('操作失败');
	}

	public function statisticalInfo(TransportCrop $model) {
		$infos = $model->getStatisticalInfo($this->input);
		foreach ($infos['list'] as &$info) {
			$info['out_box_number'] = $model->getOutBoxNumber($info);
		}

		$infos['title'] = [
			'from_warehouse' => '总仓',
			'to_warehouse' => '分仓',
			'collection_time' => '集货日期',
			'out_box_number' => '出库箱数',
			'order_number' => '集货单数',
			'box_number' => '集货箱数',
			'sent_number' => '发出箱数',
		];

		return $this->returnList('加载成功', $infos, $infos['title'], '运输统计');
	}
}
