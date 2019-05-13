<?php
// +----------------------------------------------------------------------
// | Minishop [ Easy to handle for Micro businesses ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016 http://www.qasl.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: tangtanglove <dai_hang_love@126.com> <http://www.ixiaoquan.com>
// +----------------------------------------------------------------------

use think\Db;
use think\config;


/**
 * 公共库文件
 * 主要定义系统公共函数库
 */

//云打印机.除actionprint()函数，其他函数请勿更改
/**
 * 生成签名sign
 * @param  array $params 参数
 * @param  string $apiKey API密钥
 * @param  string $msign 打印机密钥
 * @return   string sign
 */
 function generateSign($params, $apiKey,$msign)
{
    //所有请求参数按照字母先后顺序排
    ksort($params);
    //定义字符串开始所包括的字符串
    $stringToBeSigned = $apiKey;
    //把所有参数名和参数值串在一起
    foreach ($params as $k => $v)
    {
        $stringToBeSigned .= urldecode($k.$v);
    }
    unset($k, $v);
    //定义字符串结尾所包括的字符串
    $stringToBeSigned .= $msign;
    //使用MD5进行加密，再转化成大写
    return strtoupper(md5($stringToBeSigned));
}
/**
 * 生成字符串参数
 * @param array $param 参数
 * @return  string        参数字符串
 */
 function getStr($param)
{
    $str = '';
    foreach ($param as $key => $value) {
        $str=$str.$key.'='.$value.'&';
    }
    $str = rtrim($str,'&');
    return $str;
}
/**
 * 打印接口
 * @param  int $partner     用户ID
 * @param  string $machine_code 打印机终端号
 * @param  string $content      打印内容
 * @param  string $apiKey       API密钥
 * @param  string $msign       打印机密钥
 */
 function  action_print($partner,$machine_code,$content,$apiKey,$msign)
{
    $param = array(
        "partner"=>$partner,
        'machine_code'=>$machine_code,
        'time'=>time(),
        );
    //获取签名
    $param['sign'] = generateSign($param,$apiKey,$msign);
    $param['content'] = $content;
    $str = getStr($param);
    return sendCmd('http://open.10ss.net:8888',$str);
}
/**
 *  添加打印机
 * @param  int $partner     用户ID1       
 * @param  string $machine_code 打印机终端号
 * @param  string $username     用户名
 * @param  string $printname    打印机名称
 * @param  string $mobilephone  打印机卡号
 * @param  string $apiKey       API密钥
 * @param  string $msign       打印机密钥
 */
 function action_addprint($partner,$machine_code,$username,$printname,$mobilephone,$apiKey,$msign)
{
    $param = array(
        'partner'=>$partner,
        'machine_code'=>$machine_code,
        'username'=>$username,
        'printname'=>$printname,
        'mobilephone'=>$mobilephone,
        );
    $param['sign'] = generateSign($param,$apiKey,$msign);
    $param['msign'] = $msign;
    $str = getStr($param);
    return sendCmd('http://open.10ss.net:8888/addprint.php',$str);
}
/**
 * 删除打印机
 * @param  int $partner      用户ID
 * @param  string $machine_code 打印机终端号
 * @param  string $apiKey       API密钥
 * @param  string $msign        打印机密钥
 */
 function action_removeprinter($partner,$machine_code,$apiKey,$msign)
{
    $param = array(
        'partner'=>$partner,
        'machine_code'=>$machine_code,
        );
    $param['sign'] = generateSign($param,$apiKey,$msign);
    $str = getStr($param);
    return sendCmd('http://open.10ss.net:8888/removeprint.php',$str);
}
/**
 * 发起请求
 * @param  string $url  请求地址
 * @param  string $data 请求数据包
 * @return   string      请求返回数据
 */
 function sendCmd($url,$data)
{
    $curl = curl_init(); // 启动一个CURL会话      
    curl_setopt($curl, CURLOPT_URL, $url); // 要访问的地址                  
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0); // 对认证证书来源的检测    
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2); // 从证书中检查SSL加密算法是否存在      
    curl_setopt($curl, CURLOPT_HTTPHEADER, array('Expect:')); //解决数据包大不能提交     
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1); // 使用自动跳转      
    curl_setopt($curl, CURLOPT_AUTOREFERER, 1); // 自动设置Referer      
    curl_setopt($curl, CURLOPT_POST, 1); // 发送一个常规的Post请求      
    curl_setopt($curl, CURLOPT_POSTFIELDS, $data); // Post提交的数据包      
    curl_setopt($curl, CURLOPT_TIMEOUT, 30); // 设置超时限制防止死循     
    curl_setopt($curl, CURLOPT_HEADER, 0); // 显示返回的Header区域内容      
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); // 获取的信息以文件流的形式返回 
           
    $tmpInfo = curl_exec($curl); // 执行操作      
    if (curl_errno($curl)) {      
       echo 'Errno'.curl_error($curl);      
    }      
    curl_close($curl); // 关键CURL会话      
    return $tmpInfo; // 返回数据      
}
 /**
 * 连接易联云打印机，编码格式只可为utf-8
 * @param string $header    头部信息
 * @param string $table     打印内容
 * @param string $footer    尾部信息
 * @return 标准json字符串   返回状态
 * @author 天行健
 */
function actionprint($header,$footer,$table,$total_num,$total_price){  
    $time = date("Y-m-d H:i",time());
    $now_time = '时间';
    $totalnum = '总量';
    $totalprice = '合计';
    //dump($table);
    $con=null;
    //解码json
    $table = json_decode($table, true); 
    //循环遍历数组
    foreach($table as $k=>$val){
        $goods = $val['goods'];
        $number = $val['number'];
        $price = $val['price'];
        $temp="<tr><td>$goods</td><td>$number</td><td>$price</td></tr>";
        $con=$con.$temp;
    }
    //打印头部
    $con_head = '<tr><td>品名</td><td>数量</td><td>单价</td></tr>';  
    $con = $con_head.$con;
    //格式化要打印的内容
    $content =  "<center>@@2$header</center>$now_time:$time\n<table>$con</table>$totalnum:$total_num  $totalprice:$total_price\n$footer";
    //执行打印操作
    $print = action_print(config::get('y_print.partner'),
                          config::get('y_print.machine_code'),
                          $content,
                          config::get('y_print.apiKey'),
                          config::get('y_print.msign'));   
    return   $print;
    }

    // 添加打印机和删除打印机操作功能未确定
    // function addprint($username,$printname,$mobilephone){
    // //添加打印机操作
    // $add = action_addprint(config::get('y_print.partner'),
    //                          config::get('y_print.machine_code'),
    //                          $username,
    //                          $printname,
    //                          $mobilephone,
    //                          config::get('y_print.apiKey'),
    //                          config::get('y_print.msign'));
    // return   $add;
    // }

    // function deleteprint(){
    // //删除打印机操作
    // $delete=action_removeprinter(config::get('y_print.partner'),
    //                              config::get('y_print.machine_code'),
    //                              config::get('y_print.apiKey'),
    //                              config::get('y_print.msign'));
    // return   $delete;
    // }


/**
 * 系统邮件发送函数
 * @param string $to 接收邮件者邮箱
 * @param string $subject 邮件主题
 * @param string $body 邮件内容
 * @param string $attachment 附件列表
 * @return boolean
 */
function SendMail($title,$username,$time,$email,$url)
{
    $config = config('email');
    import('org.util.phpmailer.PHPMailer');
    $mail = new \PHPMailer;
    $mail->CharSet = 'utf-8';//设定邮件编码，默认ISO-8859-1，如果发中文此项必须设置，否则乱码，
    $mail->IsSMTP();//设定使用SMTP服务
    $mail->SMTPSecure = 'ssl'; 
    $mail->SMTPDebug = 1;//关闭SMTP调试功能//1=errors and messages//2=messages only
    $mail->SMTPAuth  = true;//启用SMTP验证功能
    $mail->Port      = $config['mail_port'];  // SMTP服务器的端口号
    $mail->Host      = $config['mail_smtp'];
    $mail->AddAddress($email);//添加收件人地址，
    $mail->Body      = "亲爱的" . $username . "：<br/>您在" . $time . "提交了通过邮箱".$email."找回密码请求。请点击下面的链接重置密码（按钮24小时内有效）。<br/><a href='" . $url . "' target='_blank'>" . $url . "</a><br/>如果以上链接无法点击，请将它复制到你的浏览器地址栏中进入访问。<br/>如果您没有提交找回密码请求，请忽略此邮件。"; //设置邮件正文
    $mail->From      = $config['mail_address'];//设置发件人名字
    $mail->FromName  = '管理员团队';//设置发件人名字
    $mail->Subject   = $title;//设置邮件标题
    $mail->Username  = $config['mail_loginname'];//设置用户名和密码
    $mail->Password  = $config['mail_password'];
    return($mail->Send());
} 