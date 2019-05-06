<?php

/*
 * 全局使用的自定义函数
 * 命名规则：驼峰式，以小写h开头，尽量短；若仅在当前文件使用则以_开头；
 * @author Jason
 * @version 2017-04-30
 */
use App\Exceptions\KnownLogicException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Common\Signature;
use App\Models\Logs\ApiRequestLog;

/**
 * 返回正常请求结果json 返回结果中均统一包含code
 * @param string $msg
 * @param array $data
 * @return string response
 */
function hSucceed($msg = '加载成功', $data = [])
{
    $result = ['code' => 200, 'msg' => $msg];
    if (!empty($data)) {
        $result['data'] = $data;
    }
    return response()->json($result);
}

/**
 * 返回异常请求结果json 返回结果中均统一包含code
 * @param string $msg
 * @param int $code
 * @param array $data
 * @return string response
 */
function hError($msg, $code = 0, $data = [])
{
    if (1 == $code) {
        $code = 0;
    }
    $result = ['code' => $code, 'msg' => $msg];
    if (!empty($data)) {
        $result['data'] = $data;
    }
    return response()->json($result);
}

/**
 * 返回正常请求结果
 * @param string $msg
 * @param array $data
 * @return array
 */
function hApiSucceed($msg = '请求成功', $data = []){
    $result = ['code' => 200, 'msg' => $msg, 'data' => $data];
    return $result;
}

/**
 * 返回异常请求结果
 * @param string $msg
 * @param int $code
 * @param array $data
 * @return array
 */
function hApiError($msg = '请求失败', $code = 0, $data = []){
    $result = ['code' => $code, 'msg' => $msg, 'data' => $data];
    return $result;
}
/**
 * 获取客户端IP地址
 * @return string
 */
function hGetClientIp()
{
    if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        return $_SERVER['HTTP_X_FORWARDED_FOR'];
    } elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
        return $_SERVER['HTTP_CLIENT_IP'];
    } elseif (isset($_SERVER['REMOTE_ADDR'])) {
        return $_SERVER['REMOTE_ADDR'];
    } elseif ($ip = getenv('HTTP_X_FORWARDED_FOR')) {
        return $ip;
    } elseif ($ip = getenv('HTTP_CLIENT_IP')) {
        return $ip;
    } elseif ($ip = getenv('REMOTE_ADDR')) {
        return $ip;
    }
    return '';
}

/**
 * 开启DB查询日志
 */
function hOpenDBLog()
{
    DB::enableQueryLog();
}

/**
 * 获取最近查询信息
 * @return type
 */
function hGetDBLog()
{
    return DB::getQueryLog();
}

/**
 * 获取最近查询信息
 * @return string
 */
function hGetDBLogStr($whole = true)
{
    if (!$whole) {
        return DB::getQueryLog();
    }
    $log = DB::getQueryLog();
    $query = end($log);
    $tmp = str_replace('?', '"' . '%s' . '"', $query['query']);
    return vsprintf($tmp, $query['bindings']);
}


/**
 * 使用此方法检查逻辑是否满足条件，如果检查的表达式为false，则抛出KnownLogicException，此错误可以被捕捉并且直接返回错误
 * @param  bool $checkBoolValue 需要检查的值货值表达式
 * @param string $errorMessage 检查条件不满足时应当返回的错误信息
 * @throws KnownLogicException 抛出错误
 */
function checkLogic($checkBoolValue, $errorMessage = '逻辑错误')
{
    if (!$checkBoolValue) {
        throw new KnownLogicException($errorMessage);
    }
}

/**
 * @param string $formatter 时间格式
 * @param int $timestamp 传入时间戳
 * @return false|string 返回时间
 */
function hdate($timestamp = 0, $formatter = 'Y-m-d H:i:s')
{
    if ($timestamp == 0) {
        return date($formatter, time());
    }
    return date($formatter, $timestamp);
}

/**
 * 检查字符串是否以指定字符开头
 * @param $string
 * @param $prefix
 * @return bool
 */
function hStrStartWith($string, $prefix)
{
    if ('' == $prefix || null == $prefix) {
        return true;
    }

    if ('' == $string || null == $string) {
        return false;
    }

    if (strlen($string) < strlen($prefix)) {
        return false;
    }

    if (substr($string, 0, strlen($prefix)) === $prefix) {
        return true;
    }
    return false;
}


/**
 * 检查字符串是否以指定字符结尾
 * @param $string
 * @param $prefix
 * @return bool
 */
function hStrEndWith($string, $subFix)
{
    if ('' == $subFix || null == $subFix) {
        return true;
    }

    if ('' == $string || null == $string) {
        return false;
    }

    if (strlen($string) < strlen($subFix)) {
        return false;
    }

    if (substr($string, -strlen($subFix), strlen($subFix)) === $subFix) {
        return true;
    }
    return false;
}

/**
 * @param string $prefix 前缀
 * @param string $fileExtension 文件后缀，自动添加点号
 * @return string 带前缀的时间戳文件名，不带后缀
 */
function hGetRandomFileName($prefix = '', $fileExtension = '')
{
    if ($prefix == null) {
        $prefix = '';
    }
    if ($fileExtension == null || $fileExtension == '') {
        return $prefix. (string)hGetMillisecond();
    }
    return $prefix. hGetMillisecond(). (hStrStartWith($fileExtension, '.') ? $fileExtension  : '.'. $fileExtension);
}

/**
 * @return float 获取精确的毫秒时间戳
 */
function hGetMillisecond()
{
    list($t1, $t2) = explode(' ', microtime());
    return number_format((float)sprintf('%.0f', (floatval($t1)+floatval($t2))*1000), 0, '', '');
}

/**
 * @return double 获取以小数格式返回的时间戳
 */
function hGetMillisecond2()
{
    list($t1, $t2) = explode(' ', microtime());
    return (float)sprintf('%.3f', (floatval($t1)+floatval($t2)));
}

/**
 * @param $file 文件路径
 * @return mixed 文件后缀
 */
function hGetExtension($file)
{
    return pathinfo($file, PATHINFO_EXTENSION);
}

/**
 * @param string $filePath 文件路径
 * @param string $shownName 自定义文件名，默认将显示服务器上的文件名
 * @param bool $download true 下载，false 尝试让浏览器调用关联程序打开
 */
function hDownload($filePath, $download = true, $shownName = '')
{
    if (!file_exists($filePath)) {
        return;
    }

    if ($download) {
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename='. ($shownName == ''? basename($filePath) : $shownName));
        header('Content-Transfer-Encoding: binary');
    } else {
        header('Content-Type: application/'.  (hGetExtension($filePath) == 'pdf' ? hGetExtension($filePath) :'*'));
    }

    header('Expires: 0');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Pragma: public');
    header('Content-Length: ' . filesize($filePath));
    readfile($filePath);
    exit();
}

/**
* 对象 转 数组
* @param object $object 对象
* @return array
*/
function object_to_array($object)
{
    $obj = (array)$object;
    foreach ($obj as $k => $v) {
        if (gettype($v) == 'resource') {
            return;
        }
        if (gettype($v) == 'object' || gettype($v) == 'array') {
            $obj[$k] = (array) object_to_array($v);
        }
    }
    return $obj;
}

/**
 * 将传入的数字转换为汉字表示的数字
 * @param $num
 * @return string 汉字表示的数字
 */
function hNumberInChinese($num)
{
    $c1 = "零壹贰叁肆伍陆柒捌玖";
    $c2 = "分角元拾佰仟万拾佰仟亿";
    //精确到分后面就不要了，所以只留两个小数位
    $num = round($num, 2);
    //将数字转化为整数
    $num = $num * 100;
    if (strlen($num) > 10) {
        return "金额太大，请检查";
    }
    $i = 0;
    $c = "";
    while (1) {
        if ($i == 0) {
            //获取最后一位数字
            $n = substr($num, strlen($num) - 1, 1);
        } else {
            $n = $num % 10;
        }    //每次将最后一位数字转化为中文
        $p1 = substr($c1, 3 * $n, 3);
        $p2 = substr($c2, 3 * $i, 3);
        if ($n != '0' || ($n == '0' && ($p2 == '亿' || $p2 == '万' || $p2 == '元'))) {
            $c = $p1 . $p2 . $c;
        } else {
            $c = $p1 . $c;
        }
        $i = $i + 1;
        //去掉数字最后一位了
        $num = $num / 10;
        $num = (int) $num;
        //结束循环
        if ($num == 0) {
            break;
        }
    }
    $j = 0;
    $slen = strlen($c);
    while ($j < $slen) {
        //utf8一个汉字相当3个字符
        $m = substr($c, $j, 6);
        //处理数字中很多0的情况,每次循环去掉一个汉字“零”
        if ($m == '零元' || $m == '零万' || $m == '零亿' || $m == '零零') {
            $left = substr($c, 0, $j);
            $right = substr($c, $j + 3);
            $c = $left . $right;
            $j = $j - 3;
            $slen = $slen - 3;
        }
        $j = $j + 3;
    }
    //这个是为了去掉类似23.0中最后一个“零”字
    if (substr($c, strlen($c) - 3, 3) == '零') {
        $c = substr($c, 0, strlen($c) - 3);
    }
    //将处理的汉字加上“整”
    if (empty($c)) {
        return "零元整";
    } else {
        return $c . "整";
    }
}


function hFormatException(\Exception $e, $writeLog = true)
{
    $traceString = '';
    $arr = $e->getTrace();
    foreach ($arr as $tra) {
        $traceString .= key_exists('file', $tra) ? ' FILE:'. $tra['file'] ." ": '';
        $traceString .= key_exists('line', $tra) ? ' LINE:'.$tra['line'] ." " : '';
        $traceString .= key_exists('function', $tra) ? ' FUNCTION:'.$tra['function'] ." " : '';
        $traceString .= "\n";
    }
    $msg = $e->getMessage()."\n".
                        "TRACE:".$traceString;
    if ($writeLog === true) {
        Log::error($msg);
    }
    return $msg;
}



/**
 * 判断位置是否在指定物理围栏内
 * @param array $polygon [['lat'=>x,'lng'=>x],['lat'=>x,'lng'=>x],['lat'=>x,'lng'=>x]...]
 * @param array $lnglat ['lat'=>x,'lng'=>x]
 * @return boolean
 */
function isPointInPolygon($polygon, $lnglat)
{
    $count = count($polygon);
    if ($count < 2) {
        return false;
    }
    $px = $lnglat['lat'];
    $py = $lnglat['lng'];
    $flag = false;
    for ($i = 0, $j = $count - 1; $i < $count; $j = $i, $i++) {
        $sy = $polygon[$i]['lng'];
        $sx = $polygon[$i]['lat'];
        $ty = $polygon[$j]['lng'];
        $tx = $polygon[$j]['lat'];
        // 点与多边形顶点重合
        if ($px == $sx && $py == $sy || $px == $tx && $py == $ty) {
            return true;
        }
        // 判断线段两端点是否在射线两侧
        if ($sy < $py && $ty >= $py || $sy >= $py && $ty < $py) {
            // 线段上与射线Y坐标相同的点的X坐标
            $x = $sx + ($py - $sy) * ($tx - $sx) / ($ty - $sy);
            // 点在多边形的边上
            if ($x == $px) {
                return true;
            }
            // 射线穿过多边形的边界
            if ($x > $px) {
                $flag = !$flag;
            }
        }
    }
    // 射线穿过多边形边界的次数为奇数时点在多边形内
    return $flag;
}

/**
 * 发送邮件快捷方式,仅发送到公司邮箱
 * @param string $mailMessage 邮件内容
 * @param string/array $receiver 收件人
 * @param string $subject 主题
 * @param string/array  $cc 抄送
 * @param string $attachFilePath 附件完整路径
 * @param string $fileFriendlyName
 * @return bool|string  true发送成功, string失败原因
 */
function hSentMailFast($mailMessage, $receiver, $subject, $cc = '', $attachFilePath = '', $fileFriendlyName = '')
{
    $checkCanSend =  '/.*(@ruigushop.com)$/';
    if (!is_array($cc)) {
        $cc = [];
        if (!empty($cc)) {
            $cc = [$cc];
        }
    }
    if (!is_array($receiver)) {
        $receiver = [$receiver];
    }

    if ($attachFilePath != '' && !file_exists($attachFilePath)) {
        return '附件未找到';
    }

    foreach ($receiver as $rec) {
        if (!preg_match($checkCanSend, $rec)) {
            return '非公司邮箱不可发送';
        }
    }

    foreach ($cc as $c) {
        if (!preg_match($checkCanSend, $c)) {
            return '非公司邮箱不可抄送';
        }
    }

    $msg = Mail::raw($mailMessage, function ($message) use ($receiver, $cc, $subject, $attachFilePath, $fileFriendlyName) {
        $message->from('system@ruigushop.com', 'system');
        $message->to($receiver)->subject($subject);
        $message->cc($cc)->subject($subject.'');

        if ($attachFilePath != '' && file_exists($attachFilePath)) {
            if ($fileFriendlyName == '' && $fileFriendlyName == null) {
                $attachFileName = basename($attachFilePath);
            } else {
                $attachFileName = $fileFriendlyName.pathinfo($attachFilePath, PATHINFO_EXTENSION);
            }

            // 在邮件中上传附件
            $message->attach($attachFilePath, ['as' => $attachFileName]);
        }
    });

    if (1 != $msg) {
        return '发送失败';
    }
    return true;
}

/**
 * 计算两个时间相差天数
 * @param string $oneDate 第一个时间
 * @param string $twoDate 第二个时间
 * @return integer
 */
function hCountDays($oneDate, $twoDate)
{
    $d1 = strtotime($oneDate);
    $d2 = strtotime($twoDate);
    return round(abs($d2-$d1)/3600/24);
}

/**
 * 去除数组中每个元素两边空格
 * @param array $arr
 */
function hTrimArray(array $arr)
{
    foreach ($arr as &$ar) {
        if (is_string($ar)) {
            $ar = trim($ar);
        }
    }
    return $arr;
}

/**
 * 获取数组中指定键的值
 */
function hCollapseArray(array $arr, $targetKey)
{
    return collect($arr)->transform(function ($item) use ($targetKey) {
        try {
            if (is_array($item)) {
                return key_exists($targetKey, $item) ? $item[$targetKey] : null;
            } elseif (is_object($item)) {
                return isset($item->$targetKey) ? $item->$targetKey : null;
            }
            return null;
        } catch (\Exception $ex) {
            Log::info('在数组中获取内容时发生错误'.$ex->getMessage());
            return null;
        }
    })->toArray();
}


/**
 * 填充字符串
 * @param string $prefix
 * @param string $string
 * @param string $padString
 * @param string $padLength
 * @return string string
 */
function hPadBusinessCode($prefix, $string, $padString, $padLength)
{
    return $prefix. str_pad($string, $padLength, $padString, STR_PAD_LEFT);
}

/**
 * 数组转等式连接的字符串
 * $_POST 参数转化为 key1=value1&key2=value2
 * @param array $arr
 * @return string
 */
function hArray2params($arr)
{
    $arr_params = array();
    foreach ($arr as $key => $value) {
        $arr_params[] = $key."=".$value;
    }
    return implode('&', $arr_params);
}

/**
 * curl
 * @param string $url
 * @param string $params
 * @return string
 */
function hCurlPost($url, $params='')
{
    $params = 'rg_id=web&rg_ver=9999&'.$params;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);                                        // 设置访问链接
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);                             // 是否返回信息
    curl_setopt($ch, CURLOPT_HEADER, 'Content-type: application/json');         // 设置返回信息数据格式 application/json
    curl_setopt($ch, CURLOPT_POST, true);                                       // 设置post方式提交
    curl_setopt($ch, CURLOPT_POSTFIELDS, $params);                              // POST提交数据
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);                                      // 30s超时时间
    $result = curl_exec($ch);
    $err_no = curl_errno($ch); //获取错误编号，0为正常
    curl_close($ch);
    if ($err_no) {
        error_log(print_r($result, 1));
        //error_log($err_no.$result);
        return 0;
    } else {
        if (is_null(json_decode($result))) {
            error_log(print_r($result, 1));
        }
        return $result;
    }
}

/**
 * 发起rocketmq请求
 * @param type $table
 * @param type $data
 * @return boolean
 */
function hRocketmq($table, $data)
{
    if (empty($data) || !is_array($data) || empty($table) || !is_string($table)) {
        return false;
    }
    $datetime = date('Y-m-d H:i:s');
    // 构建参数
    $content['table'] = $table;
    $content['data'] = isset($data[0]) ? $data : [$data];
    $params = [
        'topic' => 'ruigumq',
        'tag' => 'MQ',
        'key' => $table,
        'content' => json_encode($content)
    ];
    // 判断是否需要MQ
    $is_opens = DB::table('think_system')
                ->select('value')
                ->where('ename', '=', 'switch_rocketmq')
                ->first();
    $is_opens = object_to_array($is_opens);
    $is_open = $is_opens['value'];
    if (!$is_open) {
        $r_data = [
            'time' => $datetime,
            'type' => $table,
            'content' => json_encode($params),
            'status' => 0
        ];
        $status=DB::table('ruigu_rocketmq')->insertGetId($r_data);
        return !empty($status);
    }
    // 发起请求
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'http://10.10.98.88:8080/rocketmq/producer');//地址暂时写死
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
    $head = curl_exec($ch);
    curl_close($ch);
    $result = json_decode($head, true);
    if (!empty($result) && 200 == $result['code']) {
        return true;
    }
    // 如果请求超时或失败则关闭MQ并记录数据库
    $close = DB::update('update think_system set value=?,content=? where ename=?', [0,$datetime . '系统触发关闭了MQ','switch_rocketmq']);
    $res = DB::table('think_system')
                    ->where(['ename'=>'switch_rocketmq'])
                    ->update(['value'=>0,'content'=>$datetime.'系统触发关闭了MQ','switch_rocketmq']);
    if (empty($close)) {
        return false;
    }
    $s_data = [
        'time' => $datetime,
        'type' => $table,
        'content' => json_encode($params),
        'status' => 0
    ];
    $status=DB::table('ruigu_rocketmq')->insertGetId($s_data);
    return !empty($status);
}

/**
 * 判断当前是否为正式环境
 * @return bool
 */
function hIsTestEnv()
{
    return 'debug' === _ENV_FILE_PATH_;
}

function hMapValue(array $map, $value)
{
    if (isset($map[$value])) {
        return $map[$value];
    }
    return '未知'.$value;
}


/**
 * 二维数组根据某个字段去重
 */
function hArrayUnset($arr, $key)
{
    //建立一个目标数组
    $res = array();
    foreach ($arr as $value) {
        //查看有没有重复项
        if (isset($res[$value[$key]])) {
            //有：销毁
            unset($value[$key]);
        } else {
            $res[$value[$key]] = $value;
        }
    }
    return $res;
}

/**
 * 获取文件总行数
 * @param $file_path
 * @return int
 */
function hLineCount($file_path)
{
    if (!file_exists($file_path)) {
        return 0;
    }
    $file = new SplFileObject($file_path);
    $file->seek($file->getSize());
    $linesTotal = $file->key();

    return $linesTotal;
}

/**
 * 获取文件指定行
 * @param $filename
 * @param $fromLine
 * @param $toLine
 * @param bool $reversal
 * @return array
 */
function gGetFileLine($filename, $fromLine, $toLine, $reversal = false)
{
    $content = array();

    $fromLine = $fromLine <= 0 ? 1 : $fromLine;

    $count = $toLine - $fromLine;
    if ($count <= 0) {
        return $content;
    }

    $fp = new SplFileObject($filename, 'r');
    $fp->seek($fromLine -1); // 转到第N行, seek方法参数从0开始计数
    for ($i = 0; $i <= $count; ++$i) {
        if ($reversal) {
            array_unshift($content, trim($fp->current()));
        } // current()获取当前行内容
        else {
            $content[] = trim($fp->current());
        }

        $fp->next(); // 下一行
        if ($fp->eof()) {
            #array_pop($content);
            break;
        }
    }
    return array_filter($content); // array_filter过滤：false,null,''
}

/**
 * 创建文件夹
 * @param $path
 * @return mixed 返回当初创建的文件夹路径
 */
function hCreatedDIr($path)
{
    if (!file_exists($path)) {
        mkdir($path, 0777, true);
    }
    return $path;
}

/**
 * 数组去空去重
 * @param $array
 * @return $array 
 */
function array_fu($array)
{
    return array_filter(array_unique($array));    
}
/**
 * 发起POST请求
 * @param string $url
 * @param array $params
 * @param int $timeout
 * @return array
 */
function curl_post($url = '', $params = [], $timeout = 30) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);                                    // 设置访问链接
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);                         // 是否返回信息
    curl_setopt($ch, CURLOPT_HEADER, 'Content-type: application/json');     // 设置返回信息数据格式 application/json
    curl_setopt($ch, CURLOPT_POST, TRUE);                                   // 设置post方式提交
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));        // POST提交数据
    curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);                            // 超时时间
    $result = curl_exec($ch);
    $err_no = curl_errno($ch);                                              // 获取错误编号，0为正常
    curl_close($ch);
    if ($err_no) {
        if($err_no == 28){
            return hApiError('请求超时，请稍后再试');
        }else{
            return hApiError('请求失败，错误码：' . $err_no);
        }
    } elseif (is_null(json_decode($result,true))) {
        return hApiError('请求返回异常：' . $result);
    }else{
        return hApiSucceed('请求成功',json_decode($result,true));
    }
}
/**
 * 请求ERP系统接口
 * @param string $url
 * @param array $params
 * @param int $timeout
 * @return array
 */
function curl_post_erpapi($url = '', $params = [], $timeout = 30) {
    $url_adress = $url;
    Log::info('请求MPAPI，接口名称：'.$url);
    $url = env('ERP_API_URL','').$url;
    Log::info('请求MPAPI，完整地址：'.$url.' 参数：'.json_encode($params));
    $secretData = [
        'key' => env('Fin_Sign_Key', ''),
        'secret' => env('Fin_Sign_Secret', ''),
        'timestamp' => time(),
        'nonce' => rand (10000,999999),
    ];
    $signature = Signature::generate($secretData);
    $params['key'] = $secretData['key'];
    $params['timestamp'] = $secretData['timestamp'];
    $params['nonce'] = $secretData['nonce'];
    $params['signature'] = $signature;
    $result = curl_post($url,$params,$timeout);
    //api日志
    ApiRequestLog::saveLog(
        1,
        $url_adress,
        $url,
        json_encode($params),
        json_encode($result)
    );
    if ($result['code'] == 200) {
        if($result['data']['code'] == 0){   //erp返回正确数据的状态
            if (isset($result['data']['data'])) {
                return hApiSucceed('请求成功',$result['data']['data']);
            }
            return hApiSucceed($result['data']['msg']);
        }else{
            return hApiError($result['data']['msg']);
        }
    }else{
        return hApiError($result['msg']);
    }
}


/**
 * 请求ERP系统接口
 * @param string $url
 * @param array $params
 * @param int $timeout
 * @return array
 */
function curl_post_erpqueue($url = '', $params = [], $timeout = 30) {
    $url_adress = $url;
    Log::info('请求MPAPI，接口名称：'.$url);
    $url = env('ERP_API_URL','').$url;
    Log::info('请求MPAPI，完整地址：'.$url.' 参数：'.json_encode($params));
    $secretData = [
        'key' => env('Fin_Sign_Key', ''),
        'secret' => env('Fin_Sign_Secret', ''),
        'timestamp' => time(),
        'nonce' => rand (10000,999999),
    ];
    $signature = Signature::generate($secretData);
    $params['key'] = $secretData['key'];
    $params['timestamp'] = $secretData['timestamp'];
    $params['nonce'] = $secretData['nonce'];
    $params['signature'] = $signature;
    $result = curl_post($url,$params,$timeout);
    //api日志
    ApiRequestLog::saveLog(
        1,
        $url_adress,
        $url,
        json_encode($params),
        json_encode($result)
    );
    if ($result['code'] == 200) {
        if($result['data']['code'] == 200){   //erp 队列 返回正确数据的状态
            if (isset($result['data']['data'])) {
                return hApiSucceed('请求成功',$result['data']['data']);
            }
            return hApiSucceed($result['data']['msg']);
        }else{
            return hApiError($result['data']['msg']);
        }
    }else{
        return hApiError($result['msg']);
    }
}

/**
 * 请求省代系统接口
 * @param string $url
 * @param array $params
 * @param int $timeout
 * @return array
 */
function curl_post_apsapi($url = '', $params = [], $timeout = 30) {
    $url_adress = $url;
    Log::info('请求APSAPI，接口名称：'.$url);
    $url = env('APS_API_URL','').$url;
    Log::info('请求APS_API，完整地址：'.$url.' 参数：'.json_encode($params));
    $secretData = [
        'key' => env('Fin_Sign_Key', ''),
        'secret' => env('Fin_Sign_Secret', ''),
        'timestamp' => time(),
        'nonce' => rand (10000,999999),
    ];
    $signature = Signature::generate($secretData);
    $params['key'] = $secretData['key'];
    $params['timestamp'] = $secretData['timestamp'];
    $params['nonce'] = $secretData['nonce'];
    $params['signature'] = $signature;
    $result = curl_post($url,$params,$timeout);
    //api日志
    ApiRequestLog::saveLog(
        1,
        $url_adress,
        $url,
        json_encode($params),
        json_encode($result)
    );
    if ($result['code'] == 200) {
        if($result['data']['code'] == 200){   //aps返回正确数据的状态
            return hApiSucceed('请求成功',$result['data']['data']);
        }else{
            return hApiError($result['data']['message']);
        }
    }else{
        return hApiError($result['msg']);
    }
}

/**
 * 发起POST请求
 * @param string $url
 * @param array $params
 * @param int $timeout
 * @return array
 */
function curl_post_omsapi($url = '', $params = [], $timeout = 30) {
    $url_adress = $url;
    Log::info('请求OMSAPI，接口名称：'.$url);
    $url = env('OMS_API_URL','').$url;
    Log::info('请求MPAPI，完整地址：'.$url.' 参数：'.json_encode($params));
    $secretData = [
        'key' => env('Fin_Sign_Key', ''),
        'secret' => env('Fin_Sign_Secret', ''),
        'timestamp' => time(),
        'nonce' => rand (10000,999999),
    ];
    $signature = Signature::generate($secretData);
    $params['key'] = $secretData['key'];
    $params['timestamp'] = $secretData['timestamp'];
    $params['nonce'] = $secretData['nonce'];
    $params['signature'] = $signature;

    $params['rg_id'] = 'web';
    $params['rg_ver'] = '9999';
    $result = curl_post($url,$params,$timeout);

    //api日志
    ApiRequestLog::saveLog(
        1,
        $url_adress,
        $url,
        json_encode($params),
        json_encode($result)
    );

    if ($result['code'] == 200) {
        if(isset($result['data']['code']) == 200){   //erp 队列 返回正确数据的状态
            if (isset($result['data']['data'])) {
                return hApiSucceed('请求成功',$result['data']['data']);
            }
            return hApiSucceed($result['data']['msg']);
        }else{
            return hApiError($result['data']['error']);
        }
    }else{
        return hApiError($result['msg']);
    }
}

/**
 * 发起POST请求
 * @param string $url
 * @param array $params
 * @param int $timeout
 * @return array
 */
function curl_post_tsapi($url = '', $params = [], $timeout = 30) {
    $url_adress = $url;
    Log::info('请求TSAPI，接口名称：'.$url);
    $url = env('TS_API_URL','').$url;
    Log::info('请求TSAPI，完整地址：'.$url.' 参数：'.json_encode($params));
    $secretData = [
        'key' => env('Fin_Sign_Key', ''),
        'secret' => env('Fin_Sign_Secret', ''),
        'timestamp' => time(),
        'nonce' => rand (10000,999999),
    ];
    $signature = Signature::generate($secretData);
    $params['platform'] = 'tms';
    $params['key'] = $secretData['key'];
    $params['timestamp'] = $secretData['timestamp'];
    $params['nonce'] = $secretData['nonce'];
    $params['signature'] = $signature;

    $result = curl_post($url,$params,$timeout);
    
    //api日志
    ApiRequestLog::saveLog(
        1,
        $url_adress,
        $url,
        json_encode($params),
        json_encode($result)
    );

    if ($result['code'] == 200) {
        if($result['data']['code'] == 200){
            if (isset($result['data']['data'])) {
                return hApiSucceed('请求成功',$result['data']['data']);
            }
            return hApiSucceed($result['data']['message']);
        }else{
            return hApiError($result['data']['message']);
        }
    }else{
        return hApiError($result['msg']);
    }
}