<?php if (!defined('THINK_PATH')) exit(); /*a:1:{s:38:"./themes/default/index/cart_step1.html";i:1556259488;}*/ ?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<!-- 上述3个meta标签*必须*放在最前面，任何其他内容都*必须*跟随其后！ -->
<title>帆帆购物商城</title>
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
      <div class="navbar-header"> <a href="#"><p class="logo" style="font-size: 24px;color: #ffffff;">帆帆购物商城</p></a> <span class="navbar-line"></span> </div>
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
        <div class="cart-bar-left">我的购物车</div>
        <div class="cart-bar-right" style="width:1110px;"><span class="cbr-price cart-money">￥<?php echo $cartMoney; ?></span><span class="cbr-info">购物金额小计</span></div>
    </div>
    <div style="clear:both;"></div>
    <div class="cart-box">
    	<div class="cart-step">
    		<div class="cart-step1">
    			<div class="cart-step1-on"></div>
            	<div class="cart-step-title">我的购物车</div>
        	</div>
    		<div class="cart-step2">
    			<div class="cart-step2-off"></div>
            	<div class="cart-step-title">填写核对订单信息</div>
        	</div>
    		<div class="cart-step3">
    			<div class="cart-step3-off"></div>
            	<div class="cart-step-title">成功提交订单</div>
        	</div>
        </div>
    	<div style="clear:both;"></div>
        <div class="cart-list">
            <form action="<?php echo url('checkOrder'); ?>" method="post">
            <table class="table">
                <tr>
            		<td><input class="btnCheckAll" type="checkbox"> 全选</td>
            		<td width="35%">商品名称</td>
            		<td style="text-align:center;">数量</td>
            		<td>单价（元）</td>
            		<td>小计</td>
            		<td>操作</td>
            	</tr>
                <?php if(is_array($lists) || $lists instanceof \think\Collection): $i = 0; $__LIST__ = $lists;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$data): $mod = ($i % 2 );++$i;?>
                <tr>
            		<td>
                    	<span class="cart-check"><input name="goods_id" id="<?php echo $data['id']; ?>" class="goods_id" type="checkbox" <?php if(is_array($selectGoods) || $selectGoods instanceof \think\Collection): if( count($selectGoods)==0 ) : echo "" ;else: foreach($selectGoods as $key=>$vo): if($vo == $data['id']): ?>checked="checked"<?php endif; endforeach; endif; else: echo "" ;endif; ?> value="<?php echo $data['id']; ?>" /></span>
                    </td>
            		<td>
                        <a href="<?php echo url('index/goods/detail','id='.$data['id']); ?>">
                    	<span class="cart-goods">
                        	<span class="cart-goods-picture">
                            	<img src="<?php echo root_path(); ?><?php echo $data['cover_path']; ?>" width="133">
                            </span>
                        	<span class="cart-goods-info">
                                <span class="cgi-title"><?php echo $data['name']; ?></span>
                                <div style="clear:both"></div>
                                <span class="cgi-standard">规格：<?php echo $data['standard']; ?></span>
                            </span>
                        </span>
                        </a>
                    </td>
            		<td>
                    	<span class="cart-goods-num">
                        	<div class="goods-tools">
                            	<span class="jian"></span>
                                <div class="text">
                                    <input id="buy-num" type="text" value="<?php echo $data['cart_num']; ?>">
                                    <input name="cart[]" class="cart-info" id="goods_id_<?php echo $data['id']; ?>" type="hidden" value="
                                    <?php if(is_array($selectGoods) || $selectGoods instanceof \think\Collection): if( count($selectGoods)==0 ) : echo "" ;else: foreach($selectGoods as $key=>$vo): if($vo == $data['id']): ?>yes<?php endif; endforeach; endif; else: echo "" ;endif; ?>,<?php echo $data['id']; ?>,<?php echo $data['price']; ?>,<?php echo $data['cart_num']; ?>" />
                                </div>
                                <span class="jia"></span>
                            </div>
                        </span>
                    </td>
            		<td>
                    	<span class="cart-goods-price">￥<?php echo $data['price']; ?></span>
                    </td>
            		<td>
                    	<span class="cart-goods-sum goods-money-<?php echo $data['id']; ?>">￥<?php echo $data['price']*$data['cart_num']; ?>元</span>
                    </td>
            		<td>
                        <span class="cart-goods-status"><a href="#" class="delete" id="<?php echo $data['id']; ?>">删除</a></span>
                    </td>
            	</tr>
                <?php endforeach; endif; else: echo "" ;endif; ?>
                <tr>
            		<td colspan="2">
                    	<p><input class="btnCheckAll" type="checkbox"> 全选 &nbsp;&nbsp;<!--<a href="#">加入收藏</a> &nbsp;&nbsp;--><a href="#" class="deleteAll">删除</a></p>
                        <p><a href="#" id="cart-buy" class="cart-buy"></a></p>
                    </td>
            		<td></td>
            		<td colspan="3" style="font-weight:bold; text-align:right; font-size:16px">
                    	<p>购物金额小计 <span class="cart-money" style="color:#ff0024">￥<?php echo $cartMoney; ?>元</span></p>
                        <p><button class="cart-go-pay"></p>
                    </td>
           		</tr>
            </table>
        </div>
   	  <div style="clear:both;"></div>
    </div>
  </div>
</div>
<!--main end-->
<!-- jQuery (necessary for Bootstrap's JavaScript plugins) --> 
<script src="<?php echo config('theme_path'); ?>/index/js/jquery.min.js"></script>
<!-- Include all compiled plugins (below), or include individual files as needed --> 
<script src="<?php echo config('theme_path'); ?>/index/js/bootstrap.min.js"></script>
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
        $('.jian').click(function(){
                num = parseInt($(this).next().find('#buy-num').val())-1;
                if(num<=0) {
                    num = 1;
                    }
                $(this).next().find('#buy-num').val(num);
                // 修改参数
                cartInfo = $(this).next().find('.cart-info').val().split(',');
                $(this).next().find('.cart-info').val(cartInfo[0]+','+cartInfo[1]+','+cartInfo[2]+','+num);
                // 重新计算金额
                sumMoney();
            });
        $('.jia').click(function(){
                num = parseInt($(this).prev().find('#buy-num').val())+1;
                if(num<=0) {
                    num = 1;
                    }
                $(this).prev().find('#buy-num').val(num);
                // 修改参数
                cartInfo = $(this).prev().find('.cart-info').val().split(',');
                $(this).prev().find('.cart-info').val(cartInfo[0]+','+cartInfo[1]+','+cartInfo[2]+','+num);
                // 重新计算金额
                sumMoney();
            });

        // 全选
        $(".btnCheckAll").click(function () {
            if($(this).prop("checked")) {
                $(":checkbox").prop("checked", true);
                $(".cart-info").each(function(){
                    cartInfo = $(this).val().split(',');
                    $(this).val('yes'+','+cartInfo[1]+','+cartInfo[2]+','+cartInfo[3]);
                });
            } else {
                $(":checkbox").prop("checked", false);
                $(".cart-info").each(function(){
                    cartInfo = $(this).val().split(',');
                    $(this).val('no'+','+cartInfo[1]+','+cartInfo[2]+','+cartInfo[3]);
                });
            }
            // 重新计算金额
            sumMoney();
        });

        // 单个选中计算
        $(".goods_id").click(function () {
            goodsid = $(this).prop("id");
            if($(this).prop("checked")) {
                cartInfo = $("#goods_id_"+goodsid).val().split(',');
                $("#goods_id_"+goodsid).val('yes'+','+cartInfo[1]+','+cartInfo[2]+','+cartInfo[3]);
            } else {
                cartInfo = $("#goods_id_"+goodsid).val().split(',');
                $("#goods_id_"+goodsid).val('no'+','+cartInfo[1]+','+cartInfo[2]+','+cartInfo[3]);
            }
            // 重新计算金额
            sumMoney();
        });

        // js计算money
        function sumMoney() {
            var cartMoney = 0;
            $(".cart-info").each(function(){
                arrlist = $(this).val().split(',');
                if(arrlist[0].replace(/(^\s*)|(\s*$)/g, "") == 'yes') {
                    cartMoney = cartMoney+arrlist[2]*arrlist[3];
                }
                $(".goods-money-"+arrlist[1]).html('￥'+arrlist[2]*arrlist[3]+'元');
            });
            $(".cart-money").html('￥'+cartMoney+'元');
        }

        // 执行删除
        $('.delete').click(function(){
            goods_id = $(this).prop('id');
            $.ajax({
                cache: true,
                type: "POST",
                url: '<?php echo url('delete'); ?>',
                data: {id:goods_id},
                async: false,
                success: function(data) {
                    if (data.code) {
                        alert(data.msg);
                        setTimeout(function () {
                            location.href = data.url;
                        }, 1000);
                    } else {
                        alert(data.msg);
                    }

                },
                error: function(request) {
                    alert("页面错误");
                }
            });
            return false;
        });

        // 执行删除
        $('.deleteAll').click(function(){
                var goods_ids = '';
                $(".cart-info").each(function(){
                    cartInfo = $(this).val().split(',');
                    if(cartInfo[0] == 'yes') {
                        goods_ids = goods_ids+','+cartInfo[1];
                    }
                });
            $.ajax({
                cache: true,
                type: "POST",
                url: '<?php echo url('deleteAll'); ?>',
                data: {ids:goods_ids},
                async: false,
                success: function(data) {
                    if (data.code) {
                        alert(data.msg);
                        setTimeout(function () {
                            location.href = data.url;
                        }, 1000);
                    } else {
                        alert(data.msg);
                    }

                },
                error: function(request) {
                    alert("页面错误");
                }
            });
            return false;
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