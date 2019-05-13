<?php if (!defined('THINK_PATH')) exit(); /*a:1:{s:40:"./themes/default/index/user_address.html";i:1474547300;}*/ ?>
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
<link href="<?php echo config('theme_path'); ?>/index/css/user_center.css" rel="stylesheet">
<link href="<?php echo config('theme_path'); ?>/index/css/car.css" rel="stylesheet">
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
    <div class="row">
    <!--左栏 start-->
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

      <!--左栏 end-->

      <!--添加地址 start-->
      <div   class="col-xs-9 " >
        <div  class="user-center" style="height:auto">
        <p   class="user-title"><span>收货地址</span></p>
        <div class="user-content"style="height:auto">
          <div class="information-title"><span>新增收货地址</span></div>
        
          <!--不同部分 start-->
           <div class="address">
            
            <form action="" method="POST">
            <table class="input-table" style="margin-left:20px;">
                <tr>
                    <td><p><span style="color:#FF2C4C">*</span>所在地区 :</p></td>
                    <td style="padding-top:30px">
                <span id="city-list">
                        <select class="prov" id="province"></select> 
                        <select class="city" id="city" disabled="disabled"></select>
                        <select class="dist" id="county" disabled="disabled"></select>
                      </span>
              </td>
                </tr>
                <tr>
                    <td><p for="txtpswd"><span style="color:#FF2C4C">*</span>街道地址 :</p></td>
                    <td><textarea class="send-address"  name="address"id="address"
              placeholder="建议您如实填写详细收货地址,例如街道名称,门牌号码,楼层和房间号等" ></textarea></td>
                </tr>
                <tr>
                    <td><p for="txtpswd"><span style="color:#FF2C4C">*</span>收货人 :</p></td>
                    <td><input  type="text" style="height:40px !important" name=""id="consignee_name" placeholder="请输入收货人姓名" /></td>
                </tr>
                <tr>
                    <td><p for="txtpswd"><span style="color:#FF2C4C">*</span>联系电话 :<span></p></td>
                    <td><input  type="text" style="height:40px !important" name="mobile" id="mobile" placeholder="请输入电话号码"/></td>
                </tr>
                
            </table>
                          
              <div class="checkbox-address">
                <input type="checkbox" name="checkbox" value="true" aria-label="..." ><span>设为默认地址</span>
              </div>
              
              <div class="button">
                <input type="button" id="submit" value="保存"class="btn-address" />
              </div>
               </form>
              <div class="count-address">
                <p>已保存了<span class="count"><?php echo $count; ?></span>条地址,还可以保存<span class="count"><?php echo $lastcount; ?></span>条地址</p>
              </div>
                          
              <table class="table"border="0"  >
              <tr class="conservation" >
              <th >收货人</th>
              <th >所在地区</th>
              <th >街道地址</th>
              <th >电话</th>
              <th >操作</th>
              </tr>

        <?php if(is_array($addressList) || $addressList instanceof \think\Collection): $i = 0; $__LIST__ = $addressList;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$list): $mod = ($i % 2 );++$i;?>
        <tr>
          <td><?php echo $list['consignee_name']; ?></td>
          <td style="border-bottom:none"><?php echo $list['province']; ?><?php echo $list['city']; ?><?php echo $list['county']; ?></td>
          <td><?php echo $list['address']; ?></td>
          <td><?php echo $list['mobile']; ?></td>
          <input type="hidden" id="address_id" value="<?php echo $list['id']; ?>" />
          <td><a href="javascript:del(<?php echo $list['id']; ?>)"><img src=" <?php echo config('theme_path'); ?>/index/images/delete.png" /></a></td>
        </tr>
        <?php endforeach; endif; else: echo "" ;endif; ?>              
            </table>
            
          </div>
          
         <!--不同部分 end-->

        </div>
        </div>
        </div>

        <!--添加地址 end-->
      </div>
    </div>
</div>
<!--main end-->

<script src="<?php echo config('theme_path'); ?>/index/js/jquery.min.js"></script> 
<!-- Include all compiled plugins (below), or include individual files as needed --> 
<script src="<?php echo config('theme_path'); ?>/index/js/bootstrap.min.js"></script>

<!-- 城市选择 -->
<script type="text/javascript">
    var city_path = '<?php echo config('theme_path'); ?>/index/js/';
</script>
<script src="<?php echo config('theme_path'); ?>/index/js/jquery.cityselect.js"></script>

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
<!-- 客服 end -->


<script type="text/javascript" language="javascript">
  $(function(){
    $("#city-list").citySelect({
      prov:"北京",
      nodata:"none"
    });
  })
</script> 
<script type="text/javascript" language="javascript">
// 提交数据
$(function(){
  $("#submit").click(function(){
    var province =$(".prov").val();
    var city =$(".city").val();
    var county =$(".dist").val();
    
    var address           =$("#address").val();
    var consignee_name    =$("#consignee_name").val();
    var mobile            =$("#mobile").val();
    if($("input[type='checkbox']").is(':checked')==true){
      var checkvalue=2; 
    }else{
      var checkvalue=1;
    }
  $.ajax({
     type:'post',
     url:"<?php echo url('address/userAddress'); ?>",
     data:{"address":address,
          "province":province,
           "city":city, 
            "county":county,
           "consignee_name":consignee_name,
           "mobile":mobile,"checkvalue":checkvalue,},
     dataType:'json',
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
  
   })

    //高亮导航
  var curWwwPath = window.document.location.href;   
  var pathName = window.document.location.pathname; 
  // alert($('.order-content').children('div').children('span').children('a').attr('href'));
  $('.order-content').children('div').each(function(){
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

<script type="text/javascript" language="javascript">
// 删除数据
  function del(address_id){
  $.ajax({
     type:'post',
     url:"<?php echo url('address/delAddress'); ?>",
     data:{"address_id":address_id,
          },
     dataType:'json',
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
  
}
</script> 
</body>
</html>