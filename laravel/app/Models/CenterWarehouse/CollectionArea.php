<?php

namespace App\Models\CenterWarehouse;

use App\Models\BaseModel;
use Illuminate\Support\Facades\DB;

class CollectionArea extends BaseModel
{
	protected $table = 't_collection_area';

	 /**
	  * 获取分仓集货区
	  * @param $order 订单号 子单||主单
	  * @param striny 区域
	  */
	public function getCollectionArea($proviences, $regions, $city, $dealer_id){
		$collection_code = DB::table($this->table)->where([
			['proviences_code', '=', $proviences],
			['regions_code', '=', $regions],
			['city_code', '=', $city],
			['dealer_id', '=', $dealer_id]
		])->value('collection_code');

		if (!empty($collection_code)) {
			return $collection_code;
		}

		return '物流区';
	}
}