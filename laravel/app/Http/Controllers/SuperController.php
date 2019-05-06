<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;
use App\Libs\XLSXWriter\XLSXWriterContract;
use App\Libs\XLSXWriter\XLSXWriter;
/**
 * 所有controller的父类
 * Class SuperController
 * @package App\Http\Controllers
 */
class SuperController extends BaseController
{
    protected $user_id = null;
    protected $input = [];
    protected $currentRequest = null;

    /**
     * 构造方法
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        // 获取请求参数
        $this->currentRequest = $request;
        $this->input = $this->trimInput($request->all());
        $this->user_id = isset($this->input['user_id']) ? $this->input['user_id'] : null;
        if (!isset($this->input['pagesize'])
            || !is_numeric($this->input['pagesize'])
            || $this->input['pagesize'] <= 0)
        {
            $this->input['pagesize'] = 20;
        }
        $this->middleware('crossdomain');
    }

    // 获取用户名
    protected function getUserName($user_id = null){
        if (is_null($user_id)) {
            $user_id = $this->user_id;
        }
        $userInfo = User::find($user_id);
        return isset($userInfo->login_name) ? $userInfo->login_name : null;
    }
    /**
     *
     * @return int|mixed
     */
    protected function getPageSize()
    {
        return $this->input['pagesize'];
    }

    /**
     * 尝试获取用户信息
     * @param bool $throwIfNotFond 未找到用户时是否报错
     * @return mixed 用户信息
     */
    protected function getCurrentUser($throwIfNotFond = true)
    {
        static $checked = false;
        static $userInfo = null;
        if ($checked === false) {
            $userInfo = User::find($this->user_id);
            $checked = true;
        }
        if ($throwIfNotFond) {
            checkLogic($userInfo !== null, '未找到用户信息'.$this->user_id);
        }

        return $userInfo;
    }

    /**
     * 去除input中的左右空格
     * @param array $input
     * @return array
     */
    private function trimInput($input)
    {
        if (empty($input)) {
            return $input;
        }
        foreach ($input as &$value) {
            if (is_array($value)) {
                $this->trimInput($value);
            } else {
                $value = trim($value);
            }
        }
        unset($value);
        return $input;
    }


    /**
     * 尝试获取参数对象，需要获取值时需要调用value方法
     * @param string $paraName 尝试获取的参数
     * @param string $paraDesc
     * @return ApiValueObject|null
     */
    protected function getInput($paraName, $paraDesc = '')
    {
        static $resultObject = null;
        if (is_null($resultObject)) {
            $resultObject = new ApiValueObject($this->input);
        }
        $resultObject->setExceptedKey($paraName, $paraDesc);
        return $resultObject;
    }

    /**
     * 尝试获取文件
     * @param string $fileName 文件名
     * @param bool $throwFiNot 未找到文件名时是否报错
     * @return array|\Illuminate\Http\UploadedFile|null
     */
    protected function getFile($fileName, $throwFiNot = true)
    {
        $file = $this->currentRequest->file($fileName);
        if ($throwFiNot) {
            checkLogic(!empty($file), '未找到文件');
            checkLogic($file->isValid(), '文件不可用');
        }
        return $file;
    }

    /**
     * 判断当前是否在获取导出文件
     * @return bool
     */
    protected function isRequestFile()
    {
        return isset($this->input['request_file']);
    }

    /**
     *
     */
    protected function getXmlExporter()
    {
        return null;
    }

    /**
     * 统一接口返回，自动实现导出
     * @param $XLSXWriterContract object XLSXWriterContract 对象
     * @param $msg string 提示信息
     * @param $data array 响应数据
     * @param $title array Excel 标题 [$key => $val] $key是导出的表字段，$val 字段对应的标题
     * @param $exportFileName string 导出文件名
     * @return 
     */
    protected function returnList($msg = '',$data = [], $title=[], $exportFileName = '')
    {
        //导出
        if ($this->isRequestFile()) {
            $xLSXWriter = new XLSXWriter();
            $title_name = array_values($title);
            $title_key = array_keys($title);
            //标题
            $export_item[] = $title_name;
            $list = is_array($data) ? $data['list'] : $data->list;
            foreach ($list as $rr) {
                $row = [];
                for ($i=0; $i < count($title_key); $i++) {
                    $str = $title_key[$i];
                    $row[] = is_array($rr) ? (isset($rr[$str]) ? $rr[$str] : '') : $rr->$str;
                }
                $export_item[] = $row;
            }
            $xLSXWriter->export($export_item, $exportFileName.'-' . date('Y-m-d H:i:s'));
        } else {
            return hSucceed($msg,$data);
        }
    }

    protected function getInputWithDefault($inputName, $defaultValue = null)
    {
        return isset($this->input[$inputName]) ? $this->input[$inputName] : $defaultValue;
    }
}
