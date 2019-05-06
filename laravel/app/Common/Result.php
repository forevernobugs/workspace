<?php
/**
 * Created by PhpStorm.
 * Index: xiangbohua
 * Date: 28/04/2017
 * Time: 18:25
 */

namespace App\Common;

/**
 * 方法返回通用类，用来严格知名方法是否完成
 * Class Result
 * @package App\Common
 */
class Result
{
    /**
     * @var integer 成功1，失败-1
     */
    public $succeed;
    /**
     * @var string 消息返回
     */
    public $msg;

    public $data;

    public function __construct($succeed, $msg = '', $data = [])
    {
        $this->succeed = $succeed;
        $this->msg = $msg;
        $this->data = $data;
    }

    public static function build($succeed, $msg = '', $data = [])
    {
        if ($succeed === true || $succeed === false) {
            return new Result($succeed, $msg, $data);
        }
        throw new \Exception('必须指明是否成功true或者false');
    }

    public static function failure($msg = '', $data = [])
    {
        return self::Build(false, $msg, $data);
    }

    public static function succeed($msg = '', $data = [])
    {
        return self::Build(true, $msg, $data);
    }
}
