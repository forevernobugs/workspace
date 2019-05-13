<?php
namespace App\Models;

use Illuminate\Support\Facades\DB;

class LocationModel extends BaseModel
{
    /**
     * 获取区/县列表
     *
     * @return array
     */
    public function getCity()
    {
        $data = DB::table('t_location_city')->select(['city_code', 'city_name'])->get();
        return object_to_array($data);
    }

    /**
     * 获取城市列表
     *
     * @return array
     */
    public function getRegions()
    {
        $data = DB::table('t_location_regions')->select(['region_code', 'region_name'])->get();
        return object_to_array($data);
    }

    /**
     * 获取省列表
     *
     * @return array
     */
    public function getProviences()
    {
        $data = DB::table('t_location_proviences')->select(['provience_code', 'provience_name'])->get();
        return $data;
    }

    /**
     * 根据省id获取城市列表
     *
     * @return array
     */
    public function getRegionsListByProviencesId($params=[])
    {
        $data = DB::table('t_location_regions')->where('provience_code', $params['provinces_id'])->get();
        return $data;
    }

    /**
     * 根据城市id获取区/县列表
     *
     * @return array
     */
    public function getCityListByRegionsId($params=[])
    {
        $data = DB::table('t_location_city')->where('city_region_code', $params['region_id'])->get();
        return $data;
    }

    /**
     * 根据市code获取市名称
     *
     * @return array
     */
    public function getRegionNameByCode($code)
    {
        return DB::table('t_location_regions')->where('region_code', $code)->value('region_name');
    }

    /**
     * 根据省code获取省名称
     *
     * @return string
     */
    public function getProvinceNameByCode($code)
    {
        return DB::table('t_location_proviences')->where('provience_code', $code)->value('provience_name');
    }

    /**
     * 根据区/县code获取区/县名称
     *
     * @return string
     */
    public function getCityNameByCode($code)
    {
        return DB::table('t_location_city')->where('city_code', $code)->value('city_name');
    }
}