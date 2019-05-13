<?php if (!defined('THINK_PATH')) exit(); /*a:1:{s:40:"./themes/default/index/order_detail.html";i:1474532770;}*/ ?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<!-- 上述3个meta标签*必须*放在最前面，任何其他内容都*必须*跟随其后！ -->
<title><?php echo config('web_site_title'); ?></title>
<meta name="keywords" content="<?php echo config('web_site_keyword'); ?>"/>
<meta name="description" content="<?php echo config('web_site_description'); ?>"/>
<!-- Bootstrap -->
<link href="<?php echo config('theme_path'); ?>/index/css/bootstrap.css" rel="stylesheet">
<!--引用通用样式-->
<link href="<?php echo config('theme_path'); ?>/index/css/common.css" rel="stylesheet">
<link href="<?php echo config('theme_path'); ?>/index/css/order_detail.css" rel="stylesheet">

<!--[if lt IE 9]>
    <script src="<?php echo config('theme_path'); ?>/index/js/html5shiv.min.js"></script>
    <script src="<?php echo config('theme_path'); ?>/index/js/respond.min.js"></script>
<![endif]-->
</head>
<body>
<header>
  <nav class="navbar navbar-default">
    <div class="container"> 
      <!-- Brand and toggle get grouped for better mobile display -->
      <div class="navbar-header"> <a href="#"><img class="logo" src="<?php echo config('theme_path'); ?>/index/images/logo.png"></a> <span class="navbar-line"></span> </div>
      <!-- Collect the nav links, forms, and other content for toggling -->
      <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
        <ul class="nav navbar-nav">
        <?php $__NAV__ = db('navigation')->field(true)->where("hide",0)->order("sort")->select();if(is_array($__NAV__) || $__NAV__ instanceof \think\Collection): $i = 0; $__LIST__ = $__NAV__;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$nav): $mod = ($i % 2 );++$i;?>
          <li><a href="<?php echo url($nav['url']); ?>"><?php echo $nav['name']; ?></a></li>
        <?php endforeach; endif; else: echo "" ;endif; ?>
        </ul>

        <ul class="nav navbar-nav navbar-right">
          <li>
            <form class="navbar-form navbar-left" action="<?php echo url('search/index'); ?>" method="GET">
              <div class="custom-search">
                <input type="hidden" value="posts" name="module" >
                <input type="text" name="query" class="text-search" placeholder="按enter搜索">
              </div>
            </form>
          </li>
          <?php if(session('index_user_auth.nickname')): ?>
          <li class="icon-shop"><a href="<?php echo url('cart/index'); ?>"><img src="<?php echo config('theme_path'); ?>/index/images/icon_shop.png"></a></li>
          <li> <span class="login-box"><span data-toggle="dropdown" aria-haspopup="true" aria-expanded="true" style="display:block" class="name">您好，<?php echo session('index_user_auth.nickname'); ?><span class="caret"></span></span> 
          <ul class="dropdown-menu">
            <li class="i-order"><a href="<?php echo url('order/orderLists'); ?>"><span><img src="<?php echo config('theme_path'); ?>/index/images/order.png" alt=""></span>我的订单</a></li>
            <li class="i-address"><a href="<?php echo url('address/userAddress'); ?>"><span><img src="<?php echo config('theme_path'); ?>/index/images/address.png" alt=""></span>收货地址</a></li>
            <!-- <li class="i-complaint"><a href="#"><span><img src="<?php echo config('theme_path'); ?>/index/images/complaint.png" alt=""></span>投诉管理</a></li>
            <li class="i-debit"><a href="#"><span><img src="<?php echo config('theme_path'); ?>/index/images/debit.png" alt=""></span>退款管理</a></li> -->
            <li class="i-comment"><a href="<?php echo url('comment/commentlist'); ?>"><span><img src="<?php echo config('theme_path'); ?>/index/images/comment.png" alt=""></span>我的评价</a></li>
            <li class="i-collection"><a href="<?php echo url('collection/collection'); ?>"><span><img src="<?php echo config('theme_path'); ?>/index/images/collectionone.png" alt=""></span>我的收藏</a></li>
            <li class="i-member"><a href="<?php echo url('user/userCenter'); ?>"><span><img src="<?php echo config('theme_path'); ?>/index/images/member.png" alt=""></span>个人中心</a></li>
            <li class="i-logout"><a href="<?php echo url('common/logout'); ?>"><span><img src="<?php echo config('theme_path'); ?>/index/images/logout.png" alt=""></span>退出登录</a></li>
          </ul></span>
          </li>
          <?php else: ?>
          <li class="icon-shop"><a href="<?php echo url('cart/index'); ?>"><img src="<?php echo config('theme_path'); ?>/index/images/icon_shop.png"></a></li>
          <li><span class="login-register-box"><a href="<?php echo url('Index/login'); ?>">登录</a>&nbsp; | &nbsp;<a href="<?php echo url('Index/register'); ?>">注册 </a></span> </li>
          <?php endif; ?>
        </ul>
      </div>
      <!-- /.navbar-collapse --> 
    </div>
    <!-- /.container-fluid --> 
  </nav>
</header>
<!--main start-->
<div class="main">
  <div class="container">
	<div class="order-bar">
    	<div class="order-bar-left">
        订单详情
        </div>
    	<div class="order-bar-right">
        订单号：<?php echo $ordersInfo['order_no']; ?>
        </div>
    </div>
    <div style="clear:both;"></div>
    <div class="order-box">
    	<div class="order-box-left">
        	<div class="obl-bar">当前订单状态</div>
            <div class="obl-text">
            <?php switch($ordersInfo['status']): case "nopaid": ?>
                <!--等待付款 开始-->
                <div class="obl-order-wait-pay">
                    <p class="oblowp-text">等待付款</p>
                    <p><a class="oblowp-pay" href="<?php echo url('order/pay?order_no='.$ordersInfo['order_no']); ?>"></a></p>
                    <p><a class="oblowp-cancel delete" style="cursor:pointer"  data="<?php echo $ordersInfo['id']; ?>" type="1"></a></p>
                </div>
                <!--等待付款 结束-->
                <?php break; case "paid": ?><!--等待发货 开始-->
                <div class="obl-order-wait-pay">
                    <p class="oblowp-text">等待发货</p>
                    <p><a class="obloc-buy" href="<?php echo url('goods/lists'); ?>"></a></p>
                 
                </div>
                <!--等待付款 结束-->
                <?php break; case "shipped": ?>
                 <div class="obl-order-wait-pay">
                 <p class="obloc-picture"><img src="<?php echo config('theme_path'); ?>/index/images/order_complete.png"></p>
                   <p class="oblowp-text">已发货</p>
                   <p><a class="obloc-buy" href="<?php echo url('goods/lists'); ?>"></a></p>
                 
                </div>
                <?php break; case "completed": ?>
                      <!--订单完成 开始-->
                <div class="obl-order-complete">
                    <p class="obloc-picture"><img src="<?php echo config('theme_path'); ?>/index/images/order_complete.png"></p>
                    <p class="obloc-text">订单已完成</p>
                    <p><a class="obloc-buy" href="<?php echo url('goods/lists'); ?>"></a></p>
                </div>
                <!--订单完成 结束-->
                <?php break; endswitch; ?>
            	
            	
            </div>
        </div>
    	<div class="order-box-right">
        	<div class="obr-bar">订单信息</div>
            <div class="obr-text">
            	<div class="row">
                	<div class="col-md-4">
                    	<p style="margin-top:20px;">收货人信息</p>
                    	<p style="margin-top:30px;">收货人：<?php echo $ordersInfo['consignee_name']; ?></p>
                    	<p>收货地址：<?php echo $ordersInfo['address']; ?></p>
                      
                    	<p>手机号：<?php echo $ordersInfo['mobile']; ?></p>
                    </div>
                	<div class="col-md-4">
                    	<p style="margin-top:20px;">配送信息</p>
                    	<p style="margin-top:30px;">配送方式：
                        <?php if(empty($name) || ($name instanceof \think\Collection && $name->isEmpty())): ?>
                            暂无
                            <?php else: ?>
                            <?php echo $ordersInfo['express_type']; endif; ?>
                        </p>
                    	<p>送货日期：<?php echo get_delivery_time($ordersInfo['id']); ?></p>
                    </div>
                	<div class="col-md-4">
                    	<p style="margin-top:20px;">付款信息</p>
                        <p style="margin-top:30px;"class="color-red">付款金额：￥<?php echo $ordersInfo['amount']; ?>元</p>
                    	<p>付款方式：
                        <?php switch($ordersInfo['pay_type']): case "wxpay": ?>微信支付<?php break; case "alipay": ?>支付宝支付<?php break; endswitch; ?></p>
                    	<p title="<?php echo get_trade_no($ordersInfo['id']); ?>">交易号：<?php echo get_trade_no($ordersInfo['id'],20); ?></p>
                    </div>
                </div>
            </div>
        </div>
     	<div style="clear:both;"></div>
    </div>
    
    <!--商品列表 开始-->
    <div class="order-list">
    	<table class="table">
        	<tr class="order-list-title">
            	<td>商品详情</td>
            	<td>状态</td>
            	<td>单价</td>
            	<td>数量</td>
            	<td>商品总价</td>
            </tr>
            <?php if(is_array(get_orders_goods($ordersInfo['id'])) || get_orders_goods($ordersInfo['id']) instanceof \think\Collection): $i = 0; $__LIST__ = get_orders_goods($ordersInfo['id']);if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$orderlist): $mod = ($i % 2 );++$i;?>
        	<tr class="order-list-row table-border-none">
            	<td>
                	<div class="olr-left">
                    <a href="<?php echo url('goods/detail',['id'=>$orderlist['goods_id']]); ?>">
          <?php if(empty($orderlist['cover_path']) || ($orderlist['cover_path'] instanceof \think\Collection && $orderlist['cover_path']->isEmpty())): ?>
             <img src="<?php echo config('theme_path'); ?>/index/images/irc_defaut.png" class="order-img"  />
              <?php else: ?>
              <img src="<?php echo root_path(); ?><?php echo $orderlist['cover_path']; ?>" class="order-img"  />
           <?php endif; ?></a></div>
                	<div class="olr-right">
                    	<p style="margin-left:20px; margin-top:15px; font-size:16px; font-weight:bold;"><a href="#"><?php echo $orderlist['name']; ?></a></p>
                    	<p style="margin-left:20px;">规格：<?php echo $orderlist['standard']; ?></p>
                    </div>
                </td>
            	<td><span class="olr-td">
              <?php switch($ordersInfo['status']): case "nopaid": ?>未支付<?php break; case "paid": ?>已支付<?php break; case "shipped": ?>已发货<?php break; case "completed": ?>已完成<?php break; endswitch; ?></span>
              </td>
            	<td><span class="olr-td color-red">￥<?php echo $orderlist['price']; ?>元</span></td>
            	<td><span class="olr-td color-red"><?php echo $orderlist['num']; ?></span></td>
            	<td><span class="olr-td color-red">￥<?php echo $orderlist['total_money']; ?>元</span></td>
            </tr>
        	<?php endforeach; endif; else: echo "" ;endif; ?>
        	<tr class="order-list-row">
            	<td>
                </td>
            	<td></td>
            	<td></td>
            	<td colspan="2"><span class="color-red" style="float:right; font-size:16px; font-weight:bold;">￥<?php echo $ordersInfo['amount']; ?>元</span><span style="float:right;">订单总金额：</span></td>
           	</tr>
        </table>
    </div>
    <!--商品列表 结束-->
  </div>
</div>
<!--main end-->

<script src="<?php echo config('theme_path'); ?>/index/js/jquery.min.js"></script> 
<!-- Include all compiled plugins (below), or include individual files as needed --> 
<script src="<?php echo config('theme_path'); ?>/index/js/bootstrap.min.js"></script>
<!--footer start-->
<div class="footer">
	<div class="footer-main">
    	<div class="container">
			<div class="footer-main-left">
        		<ul>
            		<li>
                    	<p class="title"><a>关于我们</a></p>
						<div style="clear:both"></div>
                    	<p><a href="<?php echo url('article/page?name=company'); ?>">公司简介</a></p>
                    	<p><a href="<?php echo url('article/page?name=culture'); ?>">企业文化</a></p>
                    	<p><a href="<?php echo url('article/page?name=history'); ?>">发展历程</a></p>
                    	<p><a href="<?php echo url('article/page?name=honor'); ?>">荣誉资质</a></p>
                    </li>
            		<li>
                    	<p class="title"><a>新闻资讯</a></p>
						<div style="clear:both"></div>
                    	<p><a href="<?php echo url('article/lists?category=news'); ?>">新闻中心</a></p>
                    	<p><a href="<?php echo url('article/lists?category=info'); ?>">行业资讯</a></p>
                    </li>
            		<li>
                    	<p class="title"><a>联系我们</a></p>
						<div style="clear:both"></div>
                    	<p><a href="<?php echo url('article/page?name=address'); ?>">联系我们</a></p>
                    </li>
            	</ul>
        	</div>
			<div class="footer-main-right">
            	
            </div>
        </div>
    </div>
	<div class="footer-bottom">
    </div>
</div>
<!--[if lt IE 9]>
    <script src="STATIC_PATH/plugins/placeholders/placeholders.jquery.js"></script>
<![endif]-->
<!-- 客服 begin -->
<link href="<?php echo config('theme_path'); ?>/index/plugins/kefu/kefu.css" rel="stylesheet">
<script>
  var theme_path = "<?php echo config('theme_path'); ?>";
</script>
<script src="<?php echo config('theme_path'); ?>/index/js/common.js"></script> 
<!-- 客服 begin -->
<link href="<?php echo config('theme_path'); ?>/index/plugins/kefu/kefu.css" rel="stylesheet">
<script src="<?php echo config('theme_path'); ?>/index/plugins/kefu/kefu.js"></script> 

<div class="side">
  <ul>
    <li><a href="tencent://message/?uin=869716224&Site=www.qasl.cn&Menu=yes"><div class="sidebox"><img class="kefu-qq" src="<?php echo config('theme_path'); ?>/index/images/qq.png" style="margin-left:15px; margin-top:15px;margin-right:15px">客服中心</div></a></li>
    <li><a href="<?php echo url('article/page',['name'=>'wx']); ?>" target="_blank"><div class="sidebox"><img class="kefu-wx" src="<?php echo config('theme_path'); ?>/index/images/wx.png" style="margin-left:15px; margin-top:16px;margin-right:15px">关注微信</div></a></li>
    <li><a href="<?php echo url('article/page',['name'=>'address']); ?>" target="_blank" ><div class="sidebox"><img class="kefu-tel" src="<?php echo config('theme_path'); ?>/index/images/tel.png" style="margin-left:15px; margin-top:15px;margin-right:15px">联系我们</div></a></li>
    <li style="border:none;"><a href="javascript:goTop();" class="sidetop"><img class="kefu-top" src="<?php echo config('theme_path'); ?>/index/images/top.png" style="margin-left:15px; margin-top:20px;"></a></li>
  </ul>
</div>
<!-- 客服 end -->
<!--footer end-->
<script>
//删除或取消订单
  $('.delete').click(function(){
    id = $(this).attr('data');
    type = $(this).attr('type');
    $.ajax({
       type:'post',
       url:"<?php echo url('order/cancel'); ?>",
       data:{id:id,type:type},
       dataType:'json',
       success: function(data) {
            if (data.code) {
                alert(data.msg);
                location.href = "<?php echo url('order/orderlists'); ?>";
            } else {
                alert(data.msg);
            }
          },
          error: function(request) {
            alert("页面错误");
        }
    });
  })
</script>

</body>
</html>