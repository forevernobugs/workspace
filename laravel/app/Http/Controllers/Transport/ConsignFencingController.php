<?php
namespace App\Http\Controllers\Transport;

use App\Http\Controllers\LoginRequireController;
use App\Models\BasicInfo\ConsignFencing;

class ConsignFencingController extends LoginRequireController {
	/**
	 * 获取围栏列表
	 */
	public function fencingList(ConsignFencing $fencing) {
		$list = $fencing->getFencingList($this->input);
		foreach ($list['list'] as &$fencing) {
			$fencing['created'] = date("Y-m-d H:i:s", $fencing['created']);
			$fencing['enabled'] = $fencing['enabled'] >= 1 ? '是' : '否';
			$fencing['id'] = $fencing['dealer_id'] . ' - ' . substr($fencing['reg_code'], -3);
		}

		$title = [
			'id' => '线路编码',
			'reg_code' => '线路名称',
			'warehouse_name' => '所属仓库',
			'created_by' => '创建人',
			'enabled' => '是否启用',
			'created' => '创建时间',
		];

		return $this->returnList('加载成功', $list, $title, '线路信息');
	}
}