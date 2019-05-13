<?php
// +----------------------------------------------------------------------
// | Minishop [ Easy to handle for Micro businesses ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016 http://www.qasl.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: wangjingjing<1064868847@qq.com> <http://www.ixiaoquan.com>
// +----------------------------------------------------------------------

namespace app\wx\controller;

use think\Db;
use think\Log;
/**
 *
 * @author  ILsunshine
 */
class PayAlipay extends Base
{
	/**
	 * 支付宝 支付
	 */
	public function pay()
	{
		
		/**************************请求参数**************************/
		
		$order_no = input('get.order_no');
		
		$money = Db::name('Orders')->where(array('order_no'=>$order_no))->sum('amount');
		if(empty($money)){
			$this->error('参数错误');
		}

		//商户订单号，商户网站订单系统中唯一订单号，必填
		$out_trade_no = $order_no;
		
		//订单名称，必填
		$subject = '购买商品';
		
		//付款金额，必填
		$total_fee = $money;
		//收银台页面上，商品展示的超链接，必填
        $show_url = "http://zhoubian.qasl.cn/ddwf";
		/************************************************************/
		import('org.util.pay.Alipay.AlipayCore');
		import('org.util.pay.Alipay.AlipayMd5');
		import('org.util.pay.Alipay.AlipayNotify');
		import('org.util.pay.Alipay.AlipaySubmit');
		import('org.util.pay.Alipay.AlipayConfig');
		$ac = new \AlipayCongfig();
		$alipay_config =$ac->getcongfig() ;

		//构造要请求的参数数组，无需改动
		$parameter = array(
				"service"       => "alipay.wap.create.direct.pay.by.user",
				"partner"       => $alipay_config['partner'],
				"seller_id"  	=> $alipay_config['seller_id'],
				"payment_type"	=> $alipay_config['payment_type'],
				"notify_url"	=> $alipay_config['notify_url1'],
				"return_url"	=> $alipay_config['return_url1'],
		
				"anti_phishing_key"=>$alipay_config['anti_phishing_key'],
				"exter_invoke_ip"=>$alipay_config['exter_invoke_ip'],
				"out_trade_no"	=> $out_trade_no,
				"subject"	=> $subject,
				"total_fee"	=> $total_fee,
				"show_url"  => $show_url,
				//"total_fee"	=> '0.01',
				//"body"	=> $body,
				"_input_charset"	=> trim(strtolower($alipay_config['input_charset']))
				//其他业务参数根据在线开发文档，添加参数.文档地址:https://doc.open.alipay.com/doc2/detail.htm?spm=a219a.7629140.0.0.kiX33I&treeId=62&articleId=103740&docType=1
				//如"参数名"=>"参数值"
		
		);
		//建立请求
		
		$alipaySubmit = new \AlipaySubmit($alipay_config);
		$html_text = $alipaySubmit->buildRequestForm($parameter,"get", "确认");
		echo $html_text;
	}
	/**
	 * 
	 */
	public function return_url(){
		
		import('org.util.pay.Alipay.AlipayCore');
		import('org.util.pay.Alipay.AlipayMd5');
		import('org.util.pay.Alipay.AlipayNotify');
		import('org.util.pay.Alipay.AlipaySubmit');
		import('org.util.pay.Alipay.AlipayConfig');
		$ac = new \AlipayCongfig();
		$alipay_config =$ac->getcongfig() ;
		
		$alipayNotify = new \AlipayNotify($alipay_config);
		$verify_result = $alipayNotify->verifyReturn();
// 		var_dump($verify_result);
// 		$verify_result=true;
		$content = '支付失败';
		$out_trade_no = input('get.out_trade_no');
		if($verify_result){//验证成功
			//商户订单号
			
		
			
			//支付宝交易号
			
			$trade_no =  input('get.trade_no');
			
			//交易状态
			$trade_status = input('get.trade_status');
			
			//——请根据您的业务逻辑来编写程序（以下代码仅作参考）——
			//获取支付宝的通知返回参数，可参考技术文档中页面跳转同步通知参数列表
			$order = Db::name('Orders')->where(array('order_no'=>$out_trade_no))->find();
			
			if(empty($order)){
				$content = '订单异常！';
				#记录日志
				$logs = '[ ORDER '.date('Y-m-d H:i:s').' ] 订单：' .$out_trade_no.',支付宝流水号：'.$trade_no.'不存在！！！';
				Log::record($logs, 'error');
			}else{
				
				if($trade_status == 'TRADE_FINISHED' || $trade_status == 'TRADE_SUCCESS') {
					Db::startTrans();
					#修改订单 状态
					
					$orderdata['status'] = 'paid';
					$orderdata['is_pay'] = 1;
		
					$res = Db::name('Orders')->where(array('order_no'=>$out_trade_no))->update($orderdata);
					$logs['Orders']	= Db::name('Orders')->getLastSql();
					
					$data['order_id'] 		= $out_trade_no;
					$data['status']   		= 'paid';
					$data['createtime'] 	= time();
					$data['user'] 			= $order['uid'];
					$data['trade_no'] 		= $trade_no;
					$data['trade_status'] 	= $trade_status;
					$res1 = Db::name('OrdersStatus')->insert($data);
					$logs['OrdersStatus']	  = Db::name('OrdersStatus')->getLastSql();
					
					if($res && $res1){
						Db::commit();
					}else{
						Db::rollback();
					}
					
					#记录日志
					$logs = '[ ORDER '.date('Y-m-d H:i:s').' ] ' . var_export($logs,true);
					Log::record($logs, 'sql');			
					$content="支付成功！您的订单号是".$out_trade_no;
				
				}
				else {
					$content="订单号:".$out_trade_no."支付失败（错误码：'".$trade_status."'）";
				}
				
			}
		}
		else {
			$content="订单号支付失败!!!";
		}
		
	   $this->redirect(url('order/resultUrl',array('order_no'=>$out_trade_no)));
	}
	
	public function notify_url()
	{
		import('org.util.pay.Alipay.AlipayCore');
		import('org.util.pay.Alipay.AlipayMd5');
		import('org.util.pay.Alipay.AlipayNotify');
		import('org.util.pay.Alipay.AlipaySubmit');
		import('org.util.pay.Alipay.AlipayConfig');
		$ac = new \AlipayCongfig();
		$alipay_config =$ac->getcongfig() ;
		//计算得出通知验证结果
		$alipayNotify = new \AlipayNotify($alipay_config);
		$verify_result = $alipayNotify->verifyNotify();
		if($verify_result) {//验证成功
					//商户订单号
			
			$out_trade_no = input('get.out_trade_no');
			
			//支付宝交易号
			
			$trade_no =  input('get.trade_no');
			
			//交易状态
			$trade_status = input('get.trade_status');
			
			//——请根据您的业务逻辑来编写程序（以下代码仅作参考）——
			//获取支付宝的通知返回参数，可参考技术文档中页面跳转同步通知参数列表
			$order = Db::name('Orders')->where(array('order_no'=>$out_trade_no))->find();
			
			if(empty($order)){
			
				$content = '订单异常！';
				#记录日志
				$logs = '[ ORDER '.date('Y-m-d H:i:s').' ] 订单：' .$out_trade_no.',支付宝流水号：'.$trade_no.'不存在！！！';
				Log::record($logs, 'error');
			}
			if($trade_status == 'TRADE_FINISHED') {
				//判断该笔订单是否在商户网站中已经做过处理
				//如果没有做过处理，根据订单号（out_trade_no）在商户网站的订单系统中查到该笔订单的详细，并执行商户的业务程序
				//请务必判断请求时的total_fee、seller_id与通知时获取的total_fee、seller_id为一致的
				//如果有做过处理，不执行商户的业务程序
		
				//注意：
				//退款日期超过可退款期限后（如三个月可退款），支付宝系统发送该交易状态通知
		
				//调试用，写文本函数记录程序运行情况是否正常
				//logResult("这里写入想要调试的代码变量值，或其他运行的结果记录");
			}
			else if ($trade_status == 'TRADE_SUCCESS' && $order['status'] != 'paid') {
				Db::startTrans();
				#修改订单 状态
				$data1['status'] = 'paid';
				$data1['is_pay'] = 1;
	
				$res = Db::name('Orders')->where(array('order_no'=>$out_trade_no))->update($data1);
				$logs['Orders']	  = Db::name('Orders')->getLastSql();
				
				$data['order_id'] 		= $out_trade_no;
				$data['status']   		= 'paid';
				$data['createtime'] 	= time();
				$data['user'] 			= $order['uid'];
				$data['trade_no'] 		= $trade_no;
				$data['trade_status'] 	= $trade_status;
				$res1 = Db::name('OrdersStatus')->insert($data);
				$logs['OrdersStatus']	= Db::name('OrdersStatus')->getLastSql();
				
				if($res && $res1){
					Db::commit();
				}else{
					Db::rollback();
				}
				
				#记录日志
				$logs = '[ ORDER '.date('Y-m-d H:i:s').' ] ' . var_export($logs,true);
				Log::record($logs, 'sql');
	
			}
		
			//——请根据您的业务逻辑来编写程序（以上代码仅作参考）——
		
			echo "success";		//请不要修改或删除
			
		
			/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		}
		else {
			//验证失败
			echo "fail";
		
			//调试用，写文本函数记录程序运行情况是否正常
			//logResult("这里写入想要调试的代码变量值，或其他运行的结果记录");
		}
	}
	

}