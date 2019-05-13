<?php if (!defined('THINK_PATH')) exit(); /*a:1:{s:38:"./themes/default/index/goods_list.html";i:1556087233;}*/ ?>
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
<link href="<?php echo config('theme_path'); ?>/index/css/goods.css" rel="stylesheet">
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
	<div class="goods-cate-bar">
    	<div class="gcb-left">
        	<div class="gbc-left-title">
              全部商品
            </div>
        </div>
    	<div class="gcb-right">
        	<div class="gbc-right-list">
            	<ul>
                    <?php if(is_array($parentCate) || $parentCate instanceof \think\Collection): $k = 0; $__LIST__ = $parentCate;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$data): $mod = ($k % 2 );++$k;if($k == '1'): if($parent['name'] == $data['name']): ?>
                                <li><a href="<?php echo url('goods/lists?category='.$data['slug'].'&type='.$type); ?>" class="active"><?php echo $data['name']; ?></a></li>
                            <?php else: ?>    
                                <li><a href="<?php echo url('goods/lists?category='.$data['slug'].'&type='.$type); ?>"><?php echo $data['name']; ?></a></li>
                            <?php endif; else: if($parent['name'] == $data['name']): ?>
                                <li>|</li>
                                <li><a href="<?php echo url('goods/lists?category='.$data['slug'].'&type='.$type); ?>" class="active"><?php echo $data['name']; ?></a></li>
                            <?php else: ?>    
                                <li>|</li>
                                <li><a href="<?php echo url('goods/lists?category='.$data['slug'].'&type='.$type); ?>"><?php echo $data['name']; ?></a></li>
                            <?php endif; endif; endforeach; endif; else: echo "" ;endif; ?>

                </ul>
            </div>
            <div class="gcb-right-order">
            	<ul>
                    <?php if($type == '1'): ?>
                	<li><a href="<?php echo url('goods/lists?category='.$categoryInfo['slug']); ?>"><img src="<?php echo config('theme_path'); ?>/index/images/1.png"></a><a href="<?php echo url('goods/lists?category='.$categoryInfo['slug'].'&type=2'); ?>"><img src="<?php echo config('theme_path'); ?>/index/images/22.png"></a></li>
                    <?php else: ?>
                    <li><a href="<?php echo url('goods/lists?category='.$categoryInfo['slug']); ?>"><img src="<?php echo config('theme_path'); ?>/index/images/11.png"></a><a href="<?php echo url('goods/lists?category='.$categoryInfo['slug'].'&type=2'); ?>"><img src="<?php echo config('theme_path'); ?>/index/images/2.png"></a></li>
                    <?php endif; switch($sort): case "default": ?>
                                <li><a href="<?php echo url('goods/lists?category='.$categoryInfo['slug'].'&sort=priceup&type='.$type); ?>">价格 </a></li>
                                <li><a href="<?php echo url('goods/lists?category='.$categoryInfo['slug'].'&sort=scoreup&type='.$type); ?>">评分 </a></li>
                                <li><a href="<?php echo url('goods/lists?category='.$categoryInfo['slug'].'&sort=sellup&type='.$type); ?>">销量 </a></li>
                                <li><a href="<?php echo url('goods/lists?category='.$categoryInfo['slug'].'&sort=default&type='.$type); ?>" style="color:red">默认</a></li>
                            <?php break; case "sellup": ?>
                                <li><a href="<?php echo url('goods/lists?category='.$categoryInfo['slug'].'&sort=priceup&type='.$type); ?>">价格 </a></li>
                                <li><a href="<?php echo url('goods/lists?category='.$categoryInfo['slug'].'&sort=scoreup&type='.$type); ?>">评分</a></li>
                                <li><a href="<?php echo url('goods/lists?category='.$categoryInfo['slug'].'&sort=selldown&type='.$type); ?>">销量 <img style="margin-top:-2px;" src="<?php echo config('theme_path'); ?>/index/images/arrow_11.png"></a></li>
                                <li><a href="<?php echo url('goods/lists?category='.$categoryInfo['slug'].'&sort=default&type='.$type); ?>" style="color:red">默认</a></li>
                            <?php break; case "selldown": ?>
                                <li><a href="<?php echo url('goods/lists?category='.$categoryInfo['slug'].'&sort=priceup&type='.$type); ?>">价格 </a></li>
                                <li><a href="<?php echo url('goods/lists?category='.$categoryInfo['slug'].'&sort=scoreup&type='.$type); ?>">评分 </a></li>
                                <li><a href="<?php echo url('goods/lists?category='.$categoryInfo['slug'].'&sort=sellup&type='.$type); ?>">销量 <img style="margin-top:-2px;" src="<?php echo config('theme_path'); ?>/index/images/arrow_1.png"> </a></li>
                                <li><a href="<?php echo url('goods/lists?category='.$categoryInfo['slug'].'&sort=default&type='.$type); ?>" style="color:red">默认</a></li>
                            <?php break; case "scoreup": ?>
                                <li><a href="<?php echo url('goods/lists?category='.$categoryInfo['slug'].'&sort=priceup&type='.$type); ?>">价格 </a></li>
                                <li><a href="<?php echo url('goods/lists?category='.$categoryInfo['slug'].'&sort=scoredown&type='.$type); ?>">评分 <img style="margin-top:-2px;" src="<?php echo config('theme_path'); ?>/index/images/arrow_11.png"></a></li>
                                <li><a href="<?php echo url('goods/lists?category='.$categoryInfo['slug'].'&sort=sellup&type='.$type); ?>">销量 </a></li>
                                <li><a href="<?php echo url('goods/lists?category='.$categoryInfo['slug'].'&sort=default&type='.$type); ?>" style="color:red">默认</a></li>
                            <?php break; case "scoredown": ?>
                                <li><a href="<?php echo url('goods/lists?category='.$categoryInfo['slug'].'&sort=priceup&type='.$type); ?>">价格 </a></li>
                                <li><a href="<?php echo url('goods/lists?category='.$categoryInfo['slug'].'&sort=scoreup&type='.$type); ?>">评分 <img style="margin-top:-2px;" src="<?php echo config('theme_path'); ?>/index/images/arrow_1.png"></a></li>
                                <li><a href="<?php echo url('goods/lists?category='.$categoryInfo['slug'].'&sort=sellup&type='.$type); ?>">销量 </a></li>
                                <li><a href="<?php echo url('goods/lists?category='.$categoryInfo['slug'].'&sort=default&type='.$type); ?>" style="color:red">默认</a></li>
                            <?php break; case "priceup": ?>
                                <li><a href="<?php echo url('goods/lists?category='.$categoryInfo['slug'].'&sort=pricedown&type='.$type); ?>">价格 <img style="margin-top:-2px;" src="<?php echo config('theme_path'); ?>/index/images/arrow_11.png"></a></li>
                                <li><a href="<?php echo url('goods/lists?category='.$categoryInfo['slug'].'&sort=scoreup&type='.$type); ?>">评分 </a></li>
                                <li><a href="<?php echo url('goods/lists?category='.$categoryInfo['slug'].'&sort=sellup&type='.$type); ?>">销量 </a></li>
                                <li><a href="<?php echo url('goods/lists?category='.$categoryInfo['slug'].'&sort=default&type='.$type); ?>" style="color:red">默认</a></li>
                            <?php break; case "pricedown": ?>
                                <li><a href="<?php echo url('goods/lists?category='.$categoryInfo['slug'].'&sort=priceup&type='.$type); ?>">价格 <img style="margin-top:-2px;" src="<?php echo config('theme_path'); ?>/index/images/arrow_1.png"></a></li>
                                <li><a href="<?php echo url('goods/lists?category='.$categoryInfo['slug'].'&sort=scoreup&type='.$type); ?>">评分 </a></li>
                                <li><a href="<?php echo url('goods/lists?category='.$categoryInfo['slug'].'&sort=sellup&type='.$type); ?>">销量 </a></li>
                                <li><a href="<?php echo url('goods/lists?category='.$categoryInfo['slug'].'&sort=default&type='.$type); ?>" style="color:red">默认</a></li>
                            <?php break; default: endswitch; ?>
                	
                </ul>
            </div>
        </div>
    </div>
    
    <div class="goods-cate-box">
		<div class="gc-box-left">
        	<div class="goods-subcate-bar">
            	<ul>
                    <?php if(is_array($selfCate) || $selfCate instanceof \think\Collection): $k = 0; $__LIST__ = $selfCate;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$data): $mod = ($k % 2 );++$k;if($k == '1'): if($categoryInfo['name'] == $data['name']): ?>
                                <li><a href="<?php echo url('goods/lists?category='.$data['slug']); ?>" class="active"><?php echo $data['name']; ?></a></li>
                            <?php else: ?>    
                                <li><a href="<?php echo url('goods/lists?category='.$data['slug']); ?>"><?php echo $data['name']; ?></a></li>
                            <?php endif; else: if($categoryInfo['name'] == $data['name']): ?>
                                <li>|</li>
                                <li><a href="<?php echo url('goods/lists?category='.$data['slug']); ?>" class="active"><?php echo $data['name']; ?></a></li>
                            <?php else: ?>    
                                <li>|</li>
                                <li><a href="<?php echo url('goods/lists?category='.$data['slug']); ?>"><?php echo $data['name']; ?></a></li>
                            <?php endif; endif; endforeach; endif; else: echo "" ;endif; ?>              	
                </ul>
                <div style="clear:both;"></div>
            </div>
            <div class="goods-cate-list">
			<div class="row" style="margin-left:-5px;">
				
            <?php if($type == '1'): if(is_array($list) || $list instanceof \think\Collection): $i = 0; $__LIST__ = $list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$data): $mod = ($i % 2 );++$i;?>
                <div class="col-md-3 box-style">
                	<div class="goods-item">
                        <span class="line0">
                        <?php if(empty($data['cover_path']) || ($data['cover_path'] instanceof \think\Collection && $data['cover_path']->isEmpty())): ?>
                        <img width="220" height="138" src="<?php echo config('theme_path'); ?>./index/images/irc_defaut.png">
                        <?php else: ?>
                        <img width="220" height="138" src="<?php echo root_path(); ?><?php echo $data['cover_path']; ?>">
                        <?php endif; ?>
                        </span>
                        <span class="line1">
                        	<span class="title"><a href="<?php echo url('goods/detail?id='.$data['id']); ?>"><?php echo $data['name']; ?> </a></span>
                            <span style="float:right;"><img dataname="<?php echo $data['name']; ?>" dataprice="<?php echo $data['price']; ?>" dataid="<?php echo $data['id']; ?>" class="addcar" src="<?php echo config('theme_path'); ?>/index/images/shopping_cart.png"></span>
                        </span>
                        <span class="line2"><span class="standard">规格：<?php echo $data['standard']; ?></span></span>
                        <span class="line3"><span class="sale-num"><img src="<?php echo config('theme_path'); ?>/index/images/sales_heat.png"> 总销量：<?php echo $data['sell_num']; ?></span></a> <span class="price">￥<?php echo $data['price']; ?></span></span>
                        <span class="line4"><span class="favourable">
                        <?php switch($data['score_num']): case "1": ?><img src="<?php echo config('theme_path'); ?>/index/images/flower_1.png" alt=""><?php break; case "2": ?><img src="<?php echo config('theme_path'); ?>/index/images/flower_2.png" alt=""><?php break; case "3": ?><img src="<?php echo config('theme_path'); ?>/index/images/flower_3.png" alt=""><?php break; case "4": ?><img src="<?php echo config('theme_path'); ?>/index/images/flower_4.png" alt=""><?php break; case "5": ?><img src="<?php echo config('theme_path'); ?>/index/images/flower_5.png" alt=""><?php break; default: ?><img src="<?php echo config('theme_path'); ?>/index/images/flower_1.png" alt="">
                    <?php endswitch; ?>
                        </span> <span class="collect-num"><img src="<?php echo config('theme_path'); ?>/index/images/<?php echo get_collection_ico($data['id']); ?>" class="collection" data="<?php echo $data['id']; ?>" style="cursor: pointer;"> <span><?php echo get_collection_count($data['id']); ?></span></span></span>
                       	<a class="mark"></a>
                       	<a href="<?php echo url('goods/detail?id='.$data['id']); ?>" class="more"></a>
                        <div style="clear:both;"></div>
                    </div>
                </div>
                <?php endforeach; endif; else: echo "" ;endif; ?>
                <!--分割线-->
			<?php else: if(is_array($list) || $list instanceof \think\Collection): $i = 0; $__LIST__ = $list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$data): $mod = ($i % 2 );++$i;?>
                    <div class="line-style">
                        <div class="line-style-left">
                            <a href="<?php echo url('goods/detail?id='.$data['id']); ?>">
                             <?php if(empty($data['cover_path']) || ($data['cover_path'] instanceof \think\Collection && $data['cover_path']->isEmpty())): ?>
                                <img width="266" height="167" src="<?php echo config('theme_path'); ?>./index/images/irc_defaut.png">
                                <?php else: ?>
                                <img width="266" height="167" src="<?php echo root_path(); ?><?php echo $data['cover_path']; ?>">
                                <?php endif; ?>   
                            </a>
                        </div>
                        <div class="line-style-right">
                            <div style="width:610px; float:left;">
                                <div style="float:left;">
                                    <a style="font-size:18px; font-weight:bold; color:#333333;"  href="<?php echo url('goods/detail?id='.$data['id']); ?>"><?php echo $data['name']; ?></a>
                                </div> 
                                <span><img style="float:right; margin-right:20px;" dataname="<?php echo $data['name']; ?>" dataprice="<?php echo $data['price']; ?>" dataid="<?php echo $data['id']; ?>" class="addcar" src="<?php echo config('theme_path'); ?>/index/images/shopping_cart_big.png"></span>
                            </div>
                            <div style="width:610px; float:left; margin-top:10px;color:#666666;font-size:16px;">规格：<?php echo $data['standard']; ?></div>
                            <div style="width:610px; float:left; height:40px;">
                                <div style="float:left; color:#ff2c4c; font-size:16px; margin-top:20px;">
                                    <img src="<?php echo config('theme_path'); ?>/index/images/sales_heat_big.png"> 总销量:<?php echo $data['sell_num']; ?>
                                </div>
                                <div style="float:right;">
                                    <span style="font-size:38px; font-weight:bold; color:#333333;margin-right:20px;color:#ff2c4c;">￥<?php echo $data['price']; ?></span>
                                </div> 
                            </div>
                            <div style="width:610px; float:left; margin-top:10px;color:#666666;font-size:16px;">
                                <div style="float:left; color:#ff2c4c; font-size:16px; margin-top:20px;">
                                    <img src="<?php echo config('theme_path'); ?>/index/images/<?php echo get_collection_ico($data['id']); ?>" class="collection" data="<?php echo $data['id']; ?>" style="cursor: pointer;">&nbsp;&nbsp;<span><?php echo get_collection_count($data['id']); ?></span>
                                    &nbsp;&nbsp;&nbsp;&nbsp;
                                    <?php switch($data['score_num']): case "1": ?><img src="<?php echo config('theme_path'); ?>/index/images/flower_1.png" alt=""><?php break; case "2": ?><img src="<?php echo config('theme_path'); ?>/index/images/flower_2.png" alt=""><?php break; case "3": ?><img src="<?php echo config('theme_path'); ?>/index/images/flower_3.png" alt=""><?php break; case "4": ?><img src="<?php echo config('theme_path'); ?>/index/images/flower_4.png" alt=""><?php break; case "5": ?><img src="<?php echo config('theme_path'); ?>/index/images/flower_5.png" alt=""><?php break; default: ?><img src="<?php echo config('theme_path'); ?>/index/images/flower_1.png" alt="">
                                    <?php endswitch; ?>
                                </div>
                                <div style="float:right;margin-top:15px;">
                                    <a id="more" class="more-big-off" href="<?php echo url('goods/detail?id='.$data['id']); ?>"> </a>
                                </div> 
                            </div>
                            
                        </div>
                        <div style="clear:both;"></div>
                    </div>
                <?php endforeach; endif; else: echo "" ;endif; endif; ?>   
                
            </div>
            </div>
            <div class="page">
                <?php echo $page; ?>
            </div>
        </div>
        
		<div class="gc-box-right">
        	<div class="gcbr-bar">
            	<div class="gcbrb-left">推荐商品</div>
            </div>
            <div class="gcbr-box">
           
               <?php $__BANNER__ = db("banner")
        ->alias("a")
        ->join("banner_position b","b.id= a.position","LEFT")        
        ->where("a.status",1)->where("a.position",2)
        ->order("a.createtime")->select();if(is_array($__BANNER__) || $__BANNER__ instanceof \think\Collection): if( count($__BANNER__)==0 ) : echo "" ;else: foreach($__BANNER__ as $key=>$banner): ?>
            	<div class="gcbr-box-item">
					<a href="<?php echo url($banner['link']); ?>">
                    <img class="gcbrbi-conimg" src="<?php echo root_path(); ?><?php echo $banner['banner_path']; ?>"/></a>
                </div>
            	<?php endforeach; endif; else: echo "" ;endif; ?>
            </div>

        </div>
    </div>
    
    
    
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
<!--main end-->
<!-- jQuery (necessary for Bootstrap's JavaScript plugins) --> 
<script src="<?php echo config('theme_path'); ?>/index/js/jquery.min.js"></script> 
<!-- Include all compiled plugins (below), or include individual files as needed --> 
<script src="<?php echo config('theme_path'); ?>/index/js/bootstrap.min.js"></script>
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
    $("div #more").hover(function(){
        $(this).removeClass("more-big-off");
        $(this).addClass("more-big-on");
    },function(){
        $(this).removeClass("more-big-on");
        $(this).addClass("more-big-off");
    });

	$('.goods-item').hover(function(){
		$('.mark',this).fadeIn();
		$('.more',this).fadeIn();
	},function(){
		$('.mark',this).fadeOut();
		$('.more',this).fadeOut();
	});

    //收藏
    $('.collection').click(function(){
        id = $(this).attr('data');
        uid = "<?php echo session('index_user_auth.uid'); ?>";
        path = "<?php echo config('theme_path'); ?>/index/images/";
        collection = $(this);
        num = collection.parent().text();
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
                      collection.attr('src',path+data.data.img);
                      collection.next().text(data.data.num);
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

	//图片放大效果 开始
	$(".gcbr-box-item").hover(function() {
		$(this).find(".gcbrbi-conimg").stop().animate({"width":222,"height":271});
	}, function() {
		$(this).find(".gcbrbi-conimg").stop().animate({"width":201,"height":261});
	})
	//图片放大效果 结束
   
    //加入购物车
    $('.addcar').on('click',addProduct);



});
</script>


</body>
</html>