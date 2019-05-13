<?php
namespace App\Http\Controllers;

use App\Models\LocationModel;
use App\Http\Controllers\SuperController;

class LocationController extends SuperController
{
    /**
     * 获取省
     *
     * @return json
     */
    public function getProviences(LocationModel $location)
    {
        $data = $location->getProviences();
        return hSucceed('加载成功', ['list' => $data]);
    }

    /**
     * 获取市
     *
     * @return json
     */
    public function getRegions(LocationModel $location)
    {
        $data = $location->getRegionsListByProviencesId($this->input);
        return hSucceed('加载成功', ['list' => $data]);
    }

    /**
     * 获取区
     *
     * @return json
     */
    public function getCity(LocationModel $location)
    {
        $data = $location->getCityListByRegionsId($this->input);
        return hSucceed('加载成功', ['list' => $data]);
    }
}