<?php
/**
 * Created by PhpStorm.
 * Index: xiangbohua
 * Date: 16/01/2018
 * Time: 14:11
 */

namespace App\Http\Controllers;

use App\Exceptions\ApiParaException;
use Exception;

/**
 * 参数检查类
 * 在base controller中获取input，然后设置尝试获取参数中的值
 * 首先调用setExceptedKey 设置期望获取的参数key
 * 需要验证操作参数类型时调用isXXX系列方法校验参数
 * 支持链式操作
 * Class ApiValueObject
 * @package App\Http\Controllers\InternalApi
 */
class ApiValueObject
{
    const SUPPORTED_ROLE = [
        'bool'=>'bool类型',
        'integer'=>'整数',
        'double'=>'浮点数字',
        'string'=>'字符串',
        'object'=>'对象',
        'array'=>'数组',
        'numeric'=>'数字',
    ];
    private $inputHasValue = false;
    private $exceptedKey = '';
    private $keyDesc = '';
    private $paraCollection = [];
    private $customRole = [];

    /**
     * 构造函数
     * ApiValueObject constructor.
     * @param array $input 传入需要检查的array，通常为input
     * @param string $exceptedKey
     * @param string $keyDesc
     */
    public function __construct(array $input, $exceptedKey = '', $keyDesc = '')
    {
        $this->paraCollection = !is_array($input) ? [] : $input;
        $this->setExceptedKey($exceptedKey, $keyDesc);
    }

    /**
     * 设置期望获取的参数名称
     * @param $exceptedKey string $exceptedKey 期望在 array 的获取的key
     * @param string $keyDesc
     */
    public function setExceptedKey($exceptedKey, $keyDesc = '')
    {
        $this->keyDesc = $keyDesc;
        $this->exceptedKey = $exceptedKey === null ? '': $exceptedKey;
        $this->inputHasValue = isset($this->paraCollection[$this->exceptedKey]);
        $this->customRole = [];
    }

    /**
     * 验证即将获取的值是否为bool类型
     * @return $this
     */
    public function isBool()
    {
        $this->customCheck(function ($inputValue, $notNull) {
            if (!is_bool($inputValue)) {
                $this->throwHelper('必须是bool类型');
            }
        }, null);
        return $this;
    }

    /**
     * 验证即将获取的值是否为string类型
     * @param bool $notEmpty 如果结果为string 是否需要验证非''字符串
     * @return $this
     */
    public function isString($notEmpty = true)
    {
        $this->customCheck(function ($inputValue, $notNull) {
            if (!is_string($inputValue)) {
                $this->throwHelper('必须是字符串)');
            }
            if ($notNull == true && $inputValue == '') {
                $this->throwHelper('不能为空字符串');
            }
        }, $notEmpty);
        return $this;
    }

    /**
     * 验证即将获取的值是否为array类型
     * @param bool $notEmpty 如果结果为array 是否需要验证空array
     * @return $this
     */
    public function isArray($notEmpty = true)
    {
        $this->customCheck(function ($inputValue, $notEmpty) {
            if (!is_array($inputValue)) {
                $this->throwHelper('必须是数组');
            }

            if ($notEmpty == true && empty($inputValue)) {
                $this->throwHelper('不能为空数组');
            }
        }, $notEmpty);

        return $this;
    }

    /**
     * 验证即将获取的值是否为int类型
     * @param bool $notZero 如果结果为int 是否需要验证空非0
     * @return $this
     */
    public function isInteger($notZero = false)
    {
        $this->customCheck(function ($inputValue, $notZero) {
            if (!is_integer($inputValue)) {
                $this->throwHelper('必须是整数');
            }

            if ($notZero == true || $inputValue == 0) {
                $this->throwHelper('不能等于0');
            }
        }, $notZero);
        return $this;
    }

    /**
     * 验证即将获取的值是否为double类型
     * @param bool $notZero 如果结果为double 是否需要验证空非0
     * @return $this
     */
    public function isDouble($notZero = false)
    {
        $this->customCheck(function ($inputValue, $notZero) {
            if (!is_double($inputValue)) {
                $this->throwHelper('必须是双精度浮点数');
            }

            if ($notZero == true || $inputValue == 0) {
                $this->throwHelper('不能等于0');
            }
        }, $notZero);
        return $this;
    }

    /**
     * 验证即将获取的值是否为object类型
     * @param bool $notNull 如果结果为object 是否需要验证空非null
     * @return $this
     */
    public function isObject($notNull = false)
    {
        $this->customCheck(function ($inputValue, $notNull) {
            if (!is_object($inputValue)) {
                $this->throwHelper('必须是对象');
            }

            if ($notNull == true && $inputValue === null) {
                $this->throwHelper('对象不能为null');
            }
        }, $notNull);

        return $this;
    }


    /**
     * 自定义验证逻辑
     * @param callable $checkFun  需要验证的方法
     * @param null $additionalData 方法需要传入的附加数据
     * @return $this
     */
    public function customCheck(callable $checkFun, $additionalData = null)
    {
        $this->customRole[] = [$checkFun,$additionalData];
        return $this;
    }

    /**
     * 验证即将获取的值是否为number 不限制数据类型
     * @param bool $notZero 如果结果为number 是否需要验证空非null
     * @return $this
     */
    public function isNumeric($notZero = false)
    {
        $this->customCheck(function ($inputValue, $checkArray) {
            if (!is_numeric($inputValue)) {
                $this->throwHelper('必须是数字');
            }

            if ($checkArray == true && empty($inputValue)) {
                $this->throwHelper('不能等于0');
            }
        }, $notZero);

        return $this;
    }

    /**
     * 判断输入的是否为日期
     * @param bool $checkAfterThanNow 附加校验：是否校验不允许小于当前时间
     * @return $this
     */
    public function isDateTime($checkAfterThanNow = false)
    {
        $this->customCheck(function ($inputValue, $checkAfterThanNow) {
            $timestamp = strtotime($inputValue);
            if ($timestamp === false) {
                $this->throwHelper('必须是日期');
            }

            if ($checkAfterThanNow && $timestamp < time()) {
                $this->throwHelper('(必须大于当前时间');
            }

        }, $checkAfterThanNow);

        return $this;
    }

    /**
     * 验证即将获取的值是否存在于传入的array中
     * @param array $checkArray 如果结果为存在，继续限制期望的只是否存在于传入的array中
     * @return $this
     */
    public function inArray(array $checkArray = [])
    {
        $this->customCheck(function ($inputValue, $checkArray) {
            if (!in_array($inputValue, $checkArray)) {
                $this->throwHelper('Key必须在数组'.json_encode($checkArray).'中');
            }
        }, $checkArray);

        return $this;
    }

    /**
     * 验证传入的array中是否含有即将获取的值的key
     * @param array $checkArray 如果结果为存在，验证传入的array中是否含有即将获取的值的key
     * @return $this
     */
    public function asKeyInArray(array $checkArray = [])
    {
        $this->customCheck(function ($inputValue, $checkArray) {
            if (!key_exists($inputValue, $checkArray)) {
                $this->throwHelper('指定数组中没有名为'.$inputValue.'的key');
            }
        }, $checkArray);

        return $this;
    }

    /**
     * 获取当前值的实际值
     * 注意：当input中未找到指定key时，如果没有传入默认值，则表示值未必须的
     * @param null $defaultValueIfNotSet
     * @return mixed|null 符合条件的值
     * @throws ApiParaException 参数不满足需求时报错
     * @throws Exception 获取参数时未指定参数名称
     */
    public function value($defaultValueIfNotSet = null)
    {
        $hasDefault = func_num_args() === 1;
        if (empty($this->exceptedKey)) {
            throw new Exception('获取参数时未指定参数名称');
        }
        if (!$hasDefault && $this->inputHasValue === false) {
            $this->throwHelper('必须传入');
        }

        $returningValue = $this->inputHasValue ? $this->paraCollection[$this->exceptedKey] : $defaultValueIfNotSet;
        foreach ($this->customRole as $checkRole) {
            if (count($checkRole) == 2) {
                $closure = $checkRole[0];
                $additionValue = $checkRole[1];
                if (is_callable($closure)) {
                    $closure($returningValue, $additionValue);
                } else {
                    throw new Exception('验证器配置不正确');
                }
            } else {
                throw new Exception('验证器配置不正确');
            }
        }
        return $returningValue;
    }


    /**
     * 仅check不返回值
     * 注意：当input中未找到指定key时，如果没有传入默认值，则表示值未必须的
     * @throws ApiParaException 参数不满足需求时报错
     */
    public function check()
    {
        $this->value();
    }

    /**
     * 异常跑出帮助方法
     * @param $message
     * @throws ApiParaException
     */
    private function throwHelper($message)
    {
        if (!empty($this->keyDesc)) {
            $message = $this->keyDesc;
        } else {
            $message = '参数错误 '.$this->exceptedKey.$message;
        }

        $ex = new ApiParaException($this->exceptedKey, $message);
        throw $ex;
    }

    public function __toString()
    {
        try {
            return $this->value();
        } catch (\Exception $ex) {
            return '获取变量值失败';
        }
    }

    public function __get($name)
    {
        if ($name === 'value') {
            return $this->value();
        }
        throw new Exception('无法访问属性');
    }
}
