<?php if (!defined('THINK_PATH')) exit(); /*a:1:{s:37:"./themes/default/index/pay_index.html";i:1473386268;}*/ ?>
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
<link href="<?php echo config('theme_path'); ?>/index/css/cart.css" rel="stylesheet">
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
	<div class="cart-bar">
		<div class="cart-bar-left">成功提交订单</div>
		<div class="cart-bar-right" style="width:1094px;"></div>
    </div>
    <div style="clear:both;"></div>
    <div class="cart-box">
    	<div class="cart-step">
    		<div class="cart-step1">
    			<div class="cart-step1-off"></div>
            	<div class="cart-step-title">我的购物车</div>
        	</div>
    		<div class="cart-step2">
    			<div class="cart-step2-off"></div>
            	<div class="cart-step-title">填写核对订单信息</div>
        	</div>
    		<div class="cart-step3">
    			<div class="cart-step3-on"></div>
            	<div class="cart-step-title">成功提交订单</div>
        	</div>
        </div>
    	<div style="clear:both;"></div>
        <div class="cart-list">
			<table class="table">
				<tr>
            		<td colspan="6">
						<p style="font-size:16px; margin-top:10px;">您的订单号为：<?php echo $order_no; ?></p>
                        <p style="font-size:26px;color:#ff2c4c">
							订单提交成功，请稍后正在跳转到支付页面...
                        	<!--<div class="row">
                            	<div class="col-md-6">
                                	<div class="trc-left">
                                    	<img src="<?php echo config('theme_path'); ?>/index/images/success-on.png">
                                    </div>
                                	<div class="trc-right">
                                    	<p>我已支付成功，查看订单详情</p>
                                    	<p><a class="order-detail-button" href="#">查看订单</a></p>
                                    </div>
                                </div>
                            	<div class="col-md-6">
                                	<div class="trc-left">
                                    	<img src="<?php echo config('theme_path'); ?>/index/images/error-off.png">
                                    </div>
                                	<div class="trc-right">
                                    	<p>支付出现故障，我要重新支付</p>
                                    	<p><a class="repay-button" href="#">重新支付</a></p>
                                    </div>
                                </div>
                            </div>-->
                        </p>
                        <div style="clear:both; height:20px;"></div>
                    </td>
           		</tr>
			</table>
        </div>
   	  <div style="clear:both;"></div>
    </div>
  </div>
</div>
<!--main end-->

<!--help start-->
<div class="help">
	<div class="container">
		<div class="help-bar">
    		<div class="help-bar-left">帮助中心</div>
    		<div class="help-bar-right"></div>
    	</div>
        <div class="help-box">
			<div class="row">
  				<div class="col-md-3">
                	<div class="hbrc-left">
                    	<img class="gwzn" src="<?php echo config('theme_path'); ?>/index/images/help_icon_01.png">
                    </div>
                	<div class="hbrc-right">
                    	<div class="title">购物指南</div>
                    	<div class="text"><a href="<?php echo url('index/article/page','name=registration'); ?>">账号注册</a> | <a href="<?php echo url('index/article/page','name=process'); ?>">购物流程</a></div>
                    </div>
                </div>
  				<div class="col-md-3">
                	<div class="hbrc-left">
                    	<img class="shfw" src="<?php echo config('theme_path'); ?>/index/images/help_icon_02.png">
                    </div>
                	<div class="hbrc-right">
                    	<div class="title">售后服务</div>
                    	<div class="text"><a href="<?php echo url('index/article/page','name=payment'); ?>">先行赔付</a> | <a href="<?php echo url('index/article/page','name=refund'); ?>">退换流程</a> | <a href="<?php echo url('index/article/page','name=complain'); ?>">投诉举报</a></div>
                    </div>
                </div>
  				<div class="col-md-3">
                	<div class="hbrc-left">
                    	<img class="zffs" src="<?php echo config('theme_path'); ?>/index/images/help_icon_03.png">
                    </div>
                	<div class="hbrc-right">
                    	<div class="title">支付方式</div>
                    	<div class="text"><a href="<?php echo url('index/article/page','name=alipay'); ?>">支付宝</a> | <a href="<?php echo url('index/article/page','name=wxpay'); ?>">微信支付</a></div>
                    </div>
                </div>
  				<div class="col-md-3">
                	<div class="hbrc-left">
                    	<img class="psfs" src="<?php echo config('theme_path'); ?>/index/images/help_icon_04.png">
                    </div>
                	<div class="hbrc-right">
                    	<div class="title">配送方式</div>
                    	<div class="text"><a href="<?php echo url('index/article/page','name=distribution'); ?>">配送范围</a> | <a href="<?php echo url('index/article/page','name=freight'); ?>">运费计算</a></div>
                    </div>
                </div>
			</div>
        </div>
    </div>
</div>
<!--help end-->
<!-- jQuery (necessary for Bootstrap's JavaScript plugins) --> 
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
	$(document).ready(function(e) {
		gopay();
		function gopay() {
              setTimeout(function () {
				  window.location.href = "<?php echo url('pay',['order_no'=>$order_no]); ?>";
              }, 2000);
		}

		$('.jian').click(function(){
				num = parseInt($(this).next().find('#buy-num').val())-1;
				if(num<=0) {
					num = 1;
					}
				$(this).next().find('#buy-num').val(num);
			});
		$('.jia').click(function(){
				num = parseInt($(this).prev().find('#buy-num').val())+1;
				if(num<=0) {
					num = 1;
					}
				$(this).prev().find('#buy-num').val(num);
			});

    	$("div #cart-buy").hover(function(){
			$(this).removeClass("cart-buy");
			$(this).addClass("cart-buy-on");
		},function(){
			$(this).removeClass("cart-buy-on");
			$(this).addClass("cart-buy");
		});
		$(".gwzn").hover(function(){
			$(this).attr('src','<?php echo config('theme_path'); ?>/index/images/help_icon_011.png');
		},function(){
			$(this).attr('src','<?php echo config('theme_path'); ?>/index/images/help_icon_01.png');
		});
		$(".shfw").hover(function(){
			$(this).attr('src','<?php echo config('theme_path'); ?>/index/images/help_icon_022.png');
		},function(){
			$(this).attr('src','<?php echo config('theme_path'); ?>/index/images/help_icon_02.png');
		});
		$(".zffs").hover(function(){
			$(this).attr('src','<?php echo config('theme_path'); ?>/index/images/help_icon_033.png');
		},function(){
			$(this).attr('src','<?php echo config('theme_path'); ?>/index/images/help_icon_03.png');
		});
		$(".psfs").hover(function(){
			$(this).attr('src','<?php echo config('theme_path'); ?>/index/images/help_icon_044.png');
		},function(){
			$(this).attr('src','<?php echo config('theme_path'); ?>/index/images/help_icon_04.png');
		});
	
    });
	
</script>
</body>
</html>