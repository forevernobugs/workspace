<?php if (!defined('THINK_PATH')) exit(); /*a:1:{s:40:"./themes/default/index/goods_detail.html";i:1557758268;}*/ ?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<!-- 上述3个meta标签*必须*放在最前面，任何其他内容都*必须*跟随其后！ -->
<title>帆帆购物商城</title>

<!-- Bootstrap -->
<link href="<?php echo config('theme_path'); ?>/index/css/bootstrap.css" rel="stylesheet">
<!--引用通用样式-->
<link href="<?php echo config('theme_path'); ?>/index/css/common.css" rel="stylesheet">
<link href="<?php echo config('theme_path'); ?>/index/css/goods_detail.css" rel="stylesheet">
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
    <!-- 产品导航 -->
    <div class="goods-nav">
      
      <span class="nav-nav"><a href="<?php echo url('index/index'); ?>">首页</a> / <a href="<?php echo url('goods/lists?category=cabbage'); ?>">商品中心</a> / 详情</span>
    </div>
    <!-- 产品导航结束 -->
    <!-- 产品详情 -->
    <div class="row list">
      <!-- 左侧列表 -->
      <div class="left fl">
        <div class="show">
              <!-- <div class="show-img fl">
                <img src="./images/01.png" alt="">
              </div> -->
              <div id="preview" class="spec-preview  fl"> 
				<span class="jqzoom">
        <?php if(empty($data['photo_path_1']) || ($data['photo_path_1'] instanceof \think\Collection && $data['photo_path_1']->isEmpty())): ?>
          <img jqimg="<?php echo config('theme_path'); ?>./index/images/nopic.jpg" src="<?php echo config('theme_path'); ?>./index/images/nopic.jpg" />
        <?php else: ?>
          <img jqimg="<?php echo root_path(); ?><?php echo $data['photo_path_1']; ?>" src="<?php echo root_path(); ?><?php echo $data['photo_path_1']; ?>" />
        <?php endif; ?>
        </span> 
			  </div>
              <div class="show-info fl">
                <div class="info-title">
                  <div><?php echo $data['name']; ?></div>
                  <span>规格：<?php echo $data['standard']; ?></span>
                </div>
                <div class="info-content">
                  <div class="c-info">
                    <span class="c-money">￥<?php echo $data['price']; ?></span>
                    <span>配送：
                      <span id="city-list">
                        <select class="prov"></select> 
                        <select class="city" disabled="disabled"></select>
                        <select class="dist" disabled="disabled"></select>
                      </span>
                    </span>
                  </div>
                  <div class="c-num">
                    <div>库存数量 : </div>
                    <span ><?php echo $data['num']; ?></span>
                  </div>
                  <div class="clearfix"></div>
                  <div class="c-star">
                    <span class="c-flower">
                    <?php switch($data['score_num']): case "1": ?><img src="<?php echo config('theme_path'); ?>/index/images/flower_1.png" alt=""><?php break; case "2": ?><img src="<?php echo config('theme_path'); ?>/index/images/flower_2.png" alt=""><?php break; case "3": ?><img src="<?php echo config('theme_path'); ?>/index/images/flower_3.png" alt=""><?php break; case "4": ?><img src="<?php echo config('theme_path'); ?>/index/images/flower_4.png" alt=""><?php break; case "5": ?><img src="<?php echo config('theme_path'); ?>/index/images/flower_5.png" alt=""><?php break; default: ?><img src="<?php echo config('theme_path'); ?>/index/images/flower_1.png" alt="">
                    <?php endswitch; ?>
                    </span>|<span class="c-collection collection" data="<?php echo $data['id']; ?>" style="cursor: pointer;">人气收藏 <span><?php echo get_collection_count($data['id']); ?></span></span>|<span class="c-sale">总售量 <?php echo $data['sell_num']; ?></span>
                  </div>
                  <div class="c-but">
                    <span class="pointer" id="buy-now" dataname="<?php echo $data['name']; ?>" dataprice="<?php echo $data['price']; ?>" dataid="<?php echo $data['id']; ?>">立即购买</span>
                    <span class="pointer addcar" dataname="<?php echo $data['name']; ?>" dataprice="<?php echo $data['price']; ?>" dataid="<?php echo $data['id']; ?>">加入购物车</span>
                  </div>
                  <div class="c-img">
                    <div class="spec-scroll"> <!--暂时关闭左右移动 <a class="prev">&lt;</a> <a class="next">&gt;</a> -->
      						  <div class="items">
      						    <ul>
                        <?php if(!(empty($data['photo_path_1']) || ($data['photo_path_1'] instanceof \think\Collection && $data['photo_path_1']->isEmpty()))): ?>
                            <li><img bimg="<?php echo root_path(); ?><?php echo $data['photo_path_1']; ?>" src="<?php echo root_path(); ?><?php echo $data['photo_path_1']; ?>" onmousemove="preview(this);"></li>
                        <?php endif; if(!(empty($data['photo_path_2']) || ($data['photo_path_2'] instanceof \think\Collection && $data['photo_path_2']->isEmpty()))): ?>
                            <li><img bimg="<?php echo root_path(); ?><?php echo $data['photo_path_2']; ?>" src="<?php echo root_path(); ?><?php echo $data['photo_path_2']; ?>" onmousemove="preview(this);"></li>
                        <?php endif; if(!(empty($data['photo_path_3']) || ($data['photo_path_3'] instanceof \think\Collection && $data['photo_path_3']->isEmpty()))): ?>
                            <li><img bimg="<?php echo root_path(); ?><?php echo $data['photo_path_3']; ?>" src="<?php echo root_path(); ?><?php echo $data['photo_path_3']; ?>" onmousemove="preview(this);"></li>
                        <?php endif; ?>

      						    </ul>
      						  </div>
      						</div>
      						<div class="clearfix"></div>
                  </div>
                </div>
              </div>
              <div class="clearfix"></div>
        </div>
        <!-- 详情和评论 -->
        <div class="product-comment" style="width:960px;">
          <span id="show-detail" class="span-actived">产品详情</span>
          <span id="show-comment">评价</span>
        </div>
        <div class="detail-wap" style="width:960px;">
          <?php echo $data['content']; ?>
        </div>
        <div class="comment-wap" style="width:960px;">
          <?php if(empty($comment) || ($comment instanceof \think\Collection && $comment->isEmpty())): ?>
          <div style="padding:20px;background:#fff">
            暂无评论
          </div>
          <?php else: if(is_array($comment) || $comment instanceof \think\Collection): $i = 0; $__LIST__ = $comment;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$list): $mod = ($i % 2 );++$i;?>
          <div class="commont">
            <div class="commont-list">
                <div class="commont-info">
                  <span class="comment-phone"><?php echo get_userinfo($list['uid'],'username'); ?></span>
                  <span class="comment-flower">
                  <?php switch($list['score']): case "1": ?><img src="<?php echo config('theme_path'); ?>/index/images/flower_1.png" alt=""><?php break; case "2": ?><img src="<?php echo config('theme_path'); ?>/index/images/flower_2.png" alt=""><?php break; case "3": ?><img src="<?php echo config('theme_path'); ?>/index/images/flower_3.png" alt=""><?php break; case "4": ?><img src="<?php echo config('theme_path'); ?>/index/images/flower_4.png" alt=""><?php break; case "5": ?><img src="<?php echo config('theme_path'); ?>/index/images/flower_5.png" alt=""><?php break; default: ?><img src="<?php echo config('theme_path'); ?>/index/images/flower_1.png" alt="">
                  <?php endswitch; ?>
                  <?php echo $list['score']; ?>分</span>
                  <span class="comment-time"><?php echo date('Y-m-d',$list['createtime']); ?></span>
                  <div class="clearfix"></div>
                </div>
                <div class="commont-content">
                  <span><?php echo $list['content']; ?></span>
                </div>
            </div>
          </div>
          <?php endforeach; endif; else: echo "" ;endif; ?>
          <div class="page">
                <?php echo $page; ?>
          </div>
          <?php endif; ?>
          
        </div>
        
        <!-- 详情和评论结束 -->
      </div>
      <!-- 左侧列表 end  -->
      <!-- 右侧内容 -->
      <div class="right fr">
        <div class="hot-title"><span>最新产品</span></div>
        <?php $__GOODS__ = db('GoodsCateRelationships')->alias('a')->join('goods b','b.id= a.goods_id','LEFT')->field('b.*,a.cate_id as category')->where('b.status','1')->where('b.is_new','1')->order("id desc")->limit(2)->select();if(is_array($__GOODS__) || $__GOODS__ instanceof \think\Collection): $i = 0; $__LIST__ = $__GOODS__;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$list): $mod = ($i % 2 );++$i;?>
        <div class="hot-items">
          <div class="hot-img">
          <?php if(empty($data['cover_path']) || ($data['cover_path'] instanceof \think\Collection && $data['cover_path']->isEmpty())): ?>
              <a href="<?php echo url('goods/detail?id='.$list['id']); ?>"><img src="<?php echo config('theme_path'); ?>./index/images/irc_defaut.png" alt=""></a>
          <?php else: ?>
              <a href="<?php echo url('goods/detail?id='.$list['id']); ?>"><img src="<?php echo root_path(); ?><?php echo $list['cover_path']; ?>" alt=""></a>
          <?php endif; ?>
          </div>
          <p><?php echo $list['name']; ?></p>
        </div>
        <?php endforeach; endif; else: echo "" ;endif; ?>
        <div class="hot-title mt20"><span>总体评分</span></div>
        <div class="score-list">
            <div class="hot-score fl"><span><?php echo $data['score_num']; ?></span></div>
            <div class="hot-info fl"><div>
              <?php switch($data['score_num']): case "1": ?><img src="<?php echo config('theme_path'); ?>/index/images/flower_1.png" alt=""><?php break; case "2": ?><img src="<?php echo config('theme_path'); ?>/index/images/flower_2.png" alt=""><?php break; case "3": ?><img src="<?php echo config('theme_path'); ?>/index/images/flower_3.png" alt=""><?php break; case "4": ?><img src="<?php echo config('theme_path'); ?>/index/images/flower_4.png" alt=""><?php break; case "5": ?><img src="<?php echo config('theme_path'); ?>/index/images/flower_5.png" alt=""><?php break; default: ?><img src="<?php echo config('theme_path'); ?>/index/images/flower_1.png" alt="">
              <?php endswitch; ?>
            </div><div>共<?php echo $total_comment; ?>条评论</div></div>
            <div class="clearfix"></div>
            <div class="score-bar">
              <div class="skillbar clearfix " data-percent="<?php echo $star_5; ?>%">
                <div class="skillbar-title"><span>5分</span></div>
                <div class="skillbar-bar"></div>
                <div class="skill-bar-percent"><?php echo $star_5; ?>%</div>
              </div>
              <div class="skillbar clearfix " data-percent="<?php echo $star_4; ?>%">
                <div class="skillbar-title"><span>4分</span></div>
                <div class="skillbar-bar"></div>
                <div class="skill-bar-percent"><?php echo $star_4; ?>%</div>
              </div>
              <div class="skillbar clearfix " data-percent="<?php echo $star_3; ?>%">
                <div class="skillbar-title"><span>3分</span></div>
                <div class="skillbar-bar"></div>
                <div class="skill-bar-percent"><?php echo $star_3; ?>%</div>
              </div>
              <div class="skillbar clearfix " data-percent="<?php echo $star_2; ?>%">
                <div class="skillbar-title"><span>2分</span></div>
                <div class="skillbar-bar"></div>
                <div class="skill-bar-percent"><?php echo $star_2; ?>%</div>
              </div>
              <div class="skillbar clearfix " data-percent="<?php echo $star_1; ?>%">
                <div class="skillbar-title"><span>1分</span></div>
                <div class="skillbar-bar"></div>
                <div class="skill-bar-percent"><?php echo $star_1; ?>%</div>
              </div>
            </div>
        </div>
      </div>
      <!-- 右侧内容结束 -->
    </div>
    <!-- 产品详情结束 -->
  </div>
</div>
<!-- 购物车begin -->
<div class="car">
  <div class="car-wap">
  <div class="car-top">
    <span>购物车</span>
    <span id="car-clear">[清空]</span>
  </div>
  <div class="car-list">
    <table class="table" id="table">
      <tr class="th">
        <td class="car-list-product">产品</td>
        <td class="car-list-num">数量</td>
        <td class="car-list-price th">单价</td>
      </tr>
    </table>
  </div>
  </div>
  <div class="car-footer">
    <div id="car-icon"><i id="end"></i>共<span id="money">0.00</span>元</div>
    <a href="<?php echo url('cart/index?type=step1'); ?>" id="go"><div class="go">去结算</div></a>
  </div>
</div>
<!-- 购物车end -->
<!-- jQuery (necessary for Bootstrap's JavaScript plugins) --> 
<script src="<?php echo config('theme_path'); ?>/index/js/jquery.min.js"></script> 
<!-- Include all compiled plugins (below), or include individual files as needed --> 
<script src="<?php echo config('theme_path'); ?>/index/js/bootstrap.min.js"></script>
<!-- 城市选择 -->
<script type="text/javascript">
    var city_path = '<?php echo config('theme_path'); ?>/index/js/';
</script>
<script src="<?php echo config('theme_path'); ?>/index/js/jquery.cityselect.js"></script>
<!-- 放大 -->
<script src="<?php echo config('theme_path'); ?>/index/js/jquery.jqzoom.js"></script>
<script src="<?php echo config('theme_path'); ?>/index/js/zoom.js"></script>
<!-- 购物车 -->
<script src="<?php echo config('theme_path'); ?>/index/js/jquery.cookie.js"></script>
<script type="text/javascript">
    var car_path = '<?php echo config('theme_path'); ?>/index/images/';
    var uid = "<?php echo session('index_user_auth.uid'); ?>";
    var login_url = "<?php echo url('Base/login'); ?>";
    var forget_url = "<?php echo url('base/getPassword'); ?>";
    var reg_url = "<?php echo url('base/register'); ?>";
</script>
<script src="<?php echo config('theme_path'); ?>/index/js/jquery.car.js"></script>
<script src="STATIC_PATH/plugins/layer/layer.js"></script>
<!--main end-->
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

	$(function(){
	 	//地区联动
    $("#city-list").citySelect({
      prov:"北京",
      nodata:"none"
    });

    //评分条
    $('.skillbar').each(function(){
      $(this).find('.skillbar-bar').animate({
        width:$(this).attr('data-percent')
      },6000);
    });

    //商品详情和评论切换
    $('#show-comment').click(function(){
      $(this).addClass('span-actived');
      $('#show-detail').removeClass('span-actived');
      $('.comment-wap').css('display','block');
      $('.detail-wap').css('display','none');
    });
    $('#show-detail').click(function(){
      $(this).addClass('span-actived');
      $('#show-comment').removeClass('span-actived');
      $('.detail-wap').css('display','block');
      $('.comment-wap').css('display','none');
    });

    //加入购物车
    $('.addcar').on('click',addProduct);
    //立即购买
    $('#buy-now').click(function(){
      url = "<?php echo url('cart/index?type=step1'); ?>";
      buyNow(url,$(this));
    })
        //收藏
    $('.collection').click(function(){
        id = $(this).attr('data');
        uid = "<?php echo session('index_user_auth.uid'); ?>";
        collection = $(this);
        num = collection.children('span').text();
        if(uid){
            $.ajax({
              cache: true,
              type: "POST",
              url : '<?php echo url('collection'); ?>',
              data: {id:id,num:num},
              async: false,
                success: function(data) {
                  if (data.code) {
                      alert(data.msg);
                      collection.children('span').text(data.data.num);
                  } else {
                      alert(data.msg);
                  }

                },
                error: function(request) {
                  alert("页面错误");
                }
            });
        }else{
            alert('请先登录');
        }
        
    })

	})
</script>
</body>
</html>