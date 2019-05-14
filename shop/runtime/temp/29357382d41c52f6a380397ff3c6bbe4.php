<?php if (!defined('THINK_PATH')) exit(); /*a:1:{s:39:"./themes/default/index/user_center.html";i:1557758268;}*/ ?>
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
<link href="<?php echo config('theme_path'); ?>/index/css/user_center.css" rel="stylesheet">
<!-- <link href="<?php echo config('theme_path'); ?>/index/css/car.css" rel="stylesheet"> -->
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
<div class="main" style=" ">
  <div class="container">
    <div class="row">
    <!--左栏目 start-->
    <!--左栏 start-->
      <div class="col-xs-3 order-left-choice">
        <div class="order">
          <div class="order-left"><span>订单中心</span></div>
          <div class="order-content">
            <div><img src="<?php echo config('theme_path'); ?>/index/images/order.png" width="18px;"><span><a href="<?php echo url('order/orderLists'); ?>">我的订单</a></span></div>
            <div><img src="<?php echo config('theme_path'); ?>/index/images/comment.png" width="18px;"><span><a href="<?php echo url('comment/CommentList'); ?>">我的评价</a></span></div>
            <div><img src="<?php echo config('theme_path'); ?>/index/images/address.png" width="18px;"><span><a href="<?php echo url('address/userAddress'); ?>">收货地址</a></span></div>
            <!-- <div><img src="<?php echo config('theme_path'); ?>/index/images/complaint.png" width="18px;"><span><a href="">投诉管理</a></span></div>
            <div><img src="<?php echo config('theme_path'); ?>/index/images/debit.png" width="18px;"><span><a href="<?php echo url('order/user_refund'); ?>">退款管理</a></span></div> -->
          </div>
        </div>
        <div class="account">
          <div class="account-left"><span>我的账户</span></div>
          <div class="account-content">
            <div><img src="<?php echo config('theme_path'); ?>/index/images/member.png"><span><a href="<?php echo url('user/userCenter'); ?>">个人中心</a></span></div>
            <div><img src="<?php echo config('theme_path'); ?>/index/images/shopping.png"><span><a href="<?php echo url('cart/index'); ?>">我的购物车</a></span></div>
            <div><img src="<?php echo config('theme_path'); ?>/index/images/collectionone.png"><span><a href="<?php echo url('collection/Collection'); ?>">我的收藏</a></span></div>
          
          </div>
        </div>
        <div class="help">
          <div class="help-left"><span>帮助中心</span></div>
          <div class="help-content">
            <div class='menu'>
              <img src="<?php echo config('theme_path'); ?>/index/images/guide.png" width="18px;"><span class="show-sub">购物指南<i class="glyphicon glyphicon-chevron-down"></i></span>
              <ul>
                <li><a href="<?php echo url('Article/page?name=registration'); ?>">账号注册</a></li>
                <li><a href="<?php echo url('Article/page?name=process'); ?>">购物流程</a></li>
              </ul>
            </div>
            <div class='menu'><img src="<?php echo config('theme_path'); ?>/index/images/service.png" width="18px;"><span class="show-sub">售后服务<i class="glyphicon glyphicon-chevron-down"></i></span>
              <ul>
                <li><a href="<?php echo url('Article/page?name=payment'); ?>">先行赔付</a></li>
                <li><a href="<?php echo url('Article/page?name=refund'); ?>">退换流程</a></li>
                <li><a href="<?php echo url('Article/page?name=complain'); ?>">投诉举报</a></li>
              </ul>
            </div>
            <div class='menu'><img src="<?php echo config('theme_path'); ?>/index/images/pay.png" width="18px;"><span class="show-sub">支付方式<i class="glyphicon glyphicon-chevron-down"></i></span>
              <ul>
                <li><a href="<?php echo url('Article/page?name=alipay'); ?>">支付宝</a></li>
                <li><a href="<?php echo url('Article/page?name=wxpay'); ?>">微信支付</a></li>
              </ul>
            </div>
            <div class='menu'><img src="<?php echo config('theme_path'); ?>/index/images/distribution.png" width="18px;"><span class="show-sub">配送方式<i class="glyphicon glyphicon-chevron-down"></i></span>
              <ul>
                <li><a href="<?php echo url('Article/page?name=distribution'); ?>">配送范围</a></li>
                <li><a href="<?php echo url('Article/page?name=freight'); ?>">费用计算</a></li>
              </ul>
            </div>
          </div>
          
        </div>

      </div>

      <!--左栏 end-->
    <!--左栏目 end-->
     
      <!--个人中心 start-->
      <div class="col-xs-9 " >
       <div class="user-center">
        <p class="user-title"><span>个人中心</span></p>
        <div class="user-content">
          <div class="information-title"><span>您的基本信息</span></div>
          
<!--不同部分 start-->
          <div class="information">
            <img src="<?php echo config('theme_path'); ?>/index/images/help_icon_02.png" />
            <div class="information-text">
              <p>会员名 :<span><?php if(empty($userinfo) || ($userinfo instanceof \think\Collection && $userinfo->isEmpty())): ?>
                  未设置
                  <?php else: ?>
                  <?php echo $userinfo['username']; endif; ?></span></p>
              <p>绑定手机号 :<span style="margin-left:20px"><?php if(empty($userinfo) || ($userinfo instanceof \think\Collection && $userinfo->isEmpty())): ?>
                  未设置
                  <?php else: ?>
                  <?php echo $userinfo['mobile']; endif; ?></span></p>
              <p class="login-time">上次登录时间 :&nbsp;&nbsp;&nbsp; 
              <?php if(empty($userinfo) || ($userinfo instanceof \think\Collection && $userinfo->isEmpty())): ?>
                  这是您首次登陆
                  <?php else: ?>
                  <?php echo date("Y-m-d H:i:s",$userinfo['last_login']); endif; ?></p>
            </div>
            
          </div>
          <!--安全服务 start-->
          <div class="information-title" style="margin-top:150px"><span>您的安全服务</span></div>
          <div class="safety">
          <div class="row">
            <div class="col-md-10 col-md-offset-1">
              <table class="table" style="margin-top:20px;">
                <tr>
                  <td width="15%" style="padding-top:20px;padding-bottom:20px">
                    <div><img src="<?php echo config('theme_path'); ?>/index/images/completed.png" width="30px" height="30px" /></div>
                    <div style="margin-top:6px;color:#999">已设置</div>
                  </td>
                  <td width="15%"><div style="margin-top:10px;">登录密码</div></td>
                  <td width="50%"><div style="padding-right:20px;margin-top:10px;">安全性高的密码可以使账号更安全,建议您定期更换密码,且设置一个包含数字、字幕组合的密码,并且长度大于6位</div></td>
                  <td width="20%"><a href="<?php echo url('user/editPassword'); ?>" class="u-safe-button">修改</a></td>
                </tr>
                <tr>
                  <td  style="padding-top:20px;padding-bottom:20px">
                    <div><img src="<?php echo config('theme_path'); ?>/index/images/completed.png" width="30px" height="30px" /></div>
                    <div style="margin-top:6px;color:#999">已绑定</div>
                  </td>
                  <td><div style="margin-top:10px;">手机号</div></td>
                  <td><div style="padding-right:20px;margin-top:10px;">绑定手机号可用于账户登录,找回密码</div></td>
                  <td><a href="<?php echo url('user/editMobile'); ?>" class="u-safe-button">修改</a></td>
                </tr>
                <tr>
                  <td style="padding-top:20px;padding-bottom:20px">
                    <div><img src="<?php echo config('theme_path'); ?>/index/images/completed.png" width="30px" height="30px" /></div>
                    <div style="margin-top:6px;color:#999">已设置</div>
                  </td>
                  <td><div style="margin-top:10px;">个人资料</div></td>
                  <td><div style="padding-right:20px;margin-top:10px;">显示您的个人信息,昵称、邮箱等</div></td>
                  <td><a href="<?php echo url('user/editProfile'); ?>" class="u-safe-button">修改</a></td>
                </tr>
              </table>
            </div>
          </div>



<!--           <div class="col-xs-1" >
          </div>

           <div class="col-xs-3 safety-left" >            
              <img src="<?php echo config('theme_path'); ?>/index/images/completed.png" width="30px" height="30px" />
              <p class="safety-font">已设置</p>
              <div class="safety-pass">
              <p style="font-size:16px">登录密码</p>
             </div>
            </div>
            <div class="col-xs-5"  >
              <div class="safety-center">
              <p>安全性高的密码可以使账号更安全,建议您定期更换密码,且设置一个包含数字、字幕组合的密码,并且长度大于6位</p>
              </div>
            </div>

            <div class="col-xs-3">
            <div class="safety-right">
            <a href="#" onclick="window.location.href='<?php echo url('user/editPassword'); ?>'"><button type="button"class="safety-button"onmouseover="this.style.backgroundColor='#FF2D4B';this.style.color='#FFF'" 
             onmouseout="this.style.backgroundColor='';this.style.color='#808080'">修改</button></a>
              </div>
            </div>
            <div class="bottom-line"></div>
          </div>
        
          <div class="safety">
          <div class="col-xs-1" >
          </div>
           <div class="col-xs-3 safety-left" >            
              <img src="<?php echo config('theme_path'); ?>/index/images/completed.png" width="30px" height="30px" />
              <p class="safety-font">已绑定</p>
              <div class="safety-pass">
              <p style="font-size:16px;">手机号</p>
             </div>
            </div>
            <div class="col-xs-5">
              <div class="safety-center">
              <p>绑定手机号可用于账户登录,找回密码</p>
              </div>
            </div>

            <div class="col-xs-3">
            <div class="safety-right"style="margin-left:40px">
            <a href="#"onclick="window.location.href='<?php echo url('user/editMobile'); ?>'">
            <button type="button"class="safety-button"onmouseover="this.style.backgroundColor='#FF2D4B';this.style.color='#FFF'" 
             onmouseout="this.style.backgroundColor='';this.style.color='#808080'">修改</button></a>
            </div>
            </div>
            <div class="bottom-line"></div>
          </div>
          
          <div class="safety">
          <div class="col-xs-1" >
          </div>
           <div class="col-xs-3 safety-left" >            
              <img src="<?php echo config('theme_path'); ?>/index/images/completed.png" width="30px" height="30px" />
              <p class="safety-font">已设置</p>
              <div class="safety-pass">
              <p style="font-size:16px;">个人资料</p>
             </div>
            </div>
            <div class="col-xs-5">
              <div class="safety-center">
              <p>显示您的个人信息,昵称、邮箱等</p>
              </div>
            </div>

            <div class="col-xs-3">
            <div class="safety-right"style="margin-left:40px">
            <a href="#"onclick="window.location.href='<?php echo url('user/editProfile'); ?>'">
            <button type="button"class="safety-button"onmouseover="this.style.backgroundColor='#FF2D4B';this.style.color='#FFF'" 
             onmouseout="this.style.backgroundColor='';this.style.color='#808080'">修改</button></a>
            </div>
            </div>
            <div class="bottom-line"></div> -->
          </div>

<!--不同部分 end-->
        
        </div>
        </div>
      </div>
    </div>

</div>
</div>
<!--main end-->


<!-- Include all compiled plugins (below), or include individual files as needed --> 
<script src="<?php echo config('theme_path'); ?>/index/js/jquery.min.js"></script>  
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
  // window.onload = function(){
  //   var divs = document.getElementsByTagName(" p");
  //   var len = divs.length;
  //   for(var i=0;i<len;i++){
  //     divs[i].onclick = function(){
  //       for(var j=0;j<len;j++){
  //         divs[j].style.backgroundColor = "#FFFFFF";
  //       }
  //       this.style.backgroundColor = "red";
  //     };
  //   }
  // };

  $('.u-safe-button').mouseover(function(){
    $(this).addClass('u-safe-button-hover');
  })
   $('.u-safe-button').mouseout(function(){
    $(this).removeClass('u-safe-button-hover');
  })

  //帮助中心
  $(function(){
    //高亮导航
    var curWwwPath = window.document.location.href;   
    var pathName = window.document.location.pathname; 
    // alert($('.order-content').children('div').children('span').children('a').attr('href'));
    $('.account-content').children('div').each(function(){
       url = $(this).children('span').children('a').attr('href');
       if(url == String(pathName)){

          $(this).addClass('navhover');
       }
    });

    //帮助中心
      $('.show-sub').click(function(){
        $($(this).parent().siblings().children('ul:visible').prev('span')).toggleClass('visted');
        $($(this).parent().siblings().children('ul:visible')).slideToggle();
        $($(this).parent().siblings().children('ul:visible').prev('span').children('i')).toggleClass('glyphicon-down');

        $(this).toggleClass('visted');
        $(this).next('ul').slideToggle();
        $(this).children('i').toggleClass('glyphicon-down');
      })
  })
</script>
</body>
</html>