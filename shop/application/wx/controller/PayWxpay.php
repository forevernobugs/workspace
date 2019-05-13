<?php
// +----------------------------------------------------------------------
// | BlocksCloud [ Building app as simple as building blocks ]
// +----------------------------------------------------------------------
// | Copyright (c) 2015 http://www.blockscloud.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Ilsunshine<996218727@qq.com> <http://www.ixiaoquan.com>
// +----------------------------------------------------------------------

namespace app\wx\controller;

use think\Db;
use think\Log;
/**
 *
 * @author  Ilsunshine
 */
class PayWxpay extends Base
{
	/**
	 * 微信 支付
	 */
	public function pay()
	{				
		$order_no = input('get.order_no');
	    $money    = Db::name('Orders')->where(array('order_no'=>$order_no))->sum('amount');
		if(empty($money)){
			$this ->error('参数错误');
		}		
		// 使用jsapi接口
		import('org.util.pay.WxPayPubHelper.WxPayPubHelper');	
		$jsApi    = new \JsApi_pub();	
		    
		// 通过code获得openid
		if (!isset($_GET['code'])) {
			// 触发微信返回code码
			$Code_url = config('enter_url').'/wap/pay_wxpay/pay.html';
			$url      ="https:open.weixin.qq.com/connect/
			            oauth2/authorize?appid=".config('wxpay_appid')."
			            &redirect_uri=".$Code_url."order_no=".$order_no."
			            &response_type=code&scope=snsapi_base&state=STATE#wechat_redirect";			
			Header("Location:$url"); 	           
			exit();
		} else {		
			// 获取code码，以获取openid
		    $code    = $_GET['code'];	    
			$jsApi   ->setCode($code);
			$openid  = $jsApi->getOpenId();
		}				  		
		// 使用统一支付接口
		$unifiedOrder = new \UnifiedOrder_pub();
		$Notify_url   = config('enter_url').'/wap/pay_wxpay/notify_url.html';		
		// 设置统一支付接口参数		
		$unifiedOrder->setParameter("openid",$openid); 			// opid
		$unifiedOrder->setParameter("body","购买费用"); 		// 商品描术		
		$unifiedOrder->setParameter("out_trade_no",$order_no);  // 商户订单号
		$unifiedOrder->setParameter("total_fee",$money*100);    // 总金额 		
		$unifiedOrder->setParameter("notify_url",$Notify_url);  // 通知地址 
		$unifiedOrder->setParameter("trade_type","JSAPI");      // 交易类型
		$prepay_id   = $unifiedOrder->getPrepayId();

		// 使用jsapi调起支付		
		$jsApi           ->setPrepayId($prepay_id);
		$jsApiParameters = $jsApi->getParameters();		
		$this  ->assign('money',$money);
		$this  ->assign('order_no',$order_no);
		$this  ->assign("jsApiParameters" ,$jsApiParameters);
		$this  ->assign('openid',$openid);
		return $this->themeFetch('jspay');
	   
	}

	/**
	 * 微信支付成功后处理订单
	 */	
	public function notify_url()
	{
		// 获取返回的post数据包
		$postStr 		= $GLOBALS["HTTP_RAW_POST_DATA"] ;
		$out_trade_no   = "";		
		if (!empty($postStr)){
			libxml_disable_entity_loader(true);
			$postObj      = (array)simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);		
			$out_trade_no = $postObj['out_trade_no'];
			// 订单状态处理
			Db::startTrans();
			$data1['status'] = 'paid';
			$data1['is_pay'] = 1;
			
			$res = Db::name('Orders')->where(array('order_no'=>$out_trade_no))->update($data1);
			$logs['Orders']	         = Db::name('Orders')->getLastSql();			
			$data['order_id'] 		 = $out_trade_no;
			$data['status']   		 = 'paid';
			$data['createtime'] 	 = time();
 			$data['trade_no'] 		 = $postObj['transaction_id'];
 			$data['trade_status'] 	 = 'SUCCESS';
			$res1 = Db::name('OrdersStatus')->insert($data);		
			$logs['OrdersStatus']	 = Db::name('OrdersStatus')->getLastSql();			
			if($res && $res1) {
				Db::commit();
			} else{
				Db::rollback();
			}
			// 记录日志
			$logs   = '[ ORDER '.date('Y-m-d H:i:s').' ] ' . var_export($logs,true);
			Log::record($logs, 'success');				
			$result = "<xml>
			<return_code><![CDATA[SUCCESS]]></return_code>
			<return_msg><![CDATA[OK]]></return_msg>
			</xml>";					
		} else {
			$logs   = '[ ORDERERROR '.date('Y-m-d H:i:s').' 未接收到post数据' ;
			Log::record($logs, 'error');			
			$result = "<xml>
			<return_code><![CDATA[FAIL]]></return_code>
			<return_msg><![CDATA[未接收到post数据]]></return_msg>
			</xml>";	
		}
		echo $result;			
	}

}