<?php if (!defined('THINK_PATH')) exit(); /*a:1:{s:33:"./themes/default/index/index.html";i:1557758268;}*/ ?>
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
<link rel="shortcut icon" href="<?php echo root_path(); ?>/favicon.ico" />
<!-- Bootstrap -->
<link href="<?php echo config('theme_path'); ?>/index/css/bootstrap.css" rel="stylesheet">
<!--引用通用样式-->
<link href="<?php echo config('theme_path'); ?>/index/css/common.css" rel="stylesheet">
<link href="<?php echo config('theme_path'); ?>/index/css/index.css" rel="stylesheet">
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

<!--banner start-->
<div class="banner">
  <div class="container">
    <div data-ride="carousel" class="carousel slide" id="carousel-example-generic">
      <ol class="carousel-indicators">
		<?php $__BANNER__ = db("banner")
        ->alias("a")
        ->join("banner_position b","b.id= a.position","LEFT")        
        ->where("a.status",1)->where("a.position",1)
        ->order("a.createtime")->select();if(is_array($__BANNER__) || $__BANNER__ instanceof \think\Collection): if( count($__BANNER__)==0 ) : echo "" ;else: foreach($__BANNER__ as $key=>$banner): ?>
		    <li data-slide-to="<?php echo $key; ?>" data-target="#carousel-example-generic" class="<?php if($key == '0'): ?>active<?php endif; ?>"></li>
		<?php endforeach; endif; else: echo "" ;endif; ?>
      </ol>
      <div role="listbox" class="carousel-inner">
		<?php $__BANNER__ = db("banner")
        ->alias("a")
        ->join("banner_position b","b.id= a.position","LEFT")        
        ->where("a.status",1)->where("a.position",1)
        ->order("a.createtime")->select();if(is_array($__BANNER__) || $__BANNER__ instanceof \think\Collection): if( count($__BANNER__)==0 ) : echo "" ;else: foreach($__BANNER__ as $key=>$banner): ?>
			<div class="item <?php if($key == '0'): ?>active<?php endif; ?>">
				<a href="<?php echo url($banner['link']); ?>"><img alt="title" src="<?php echo root_path(); ?><?php echo $banner['banner_path']; ?>" data-holder-rendered="true"></a>
			</div>
		<?php endforeach; endif; else: echo "" ;endif; ?>
      </div>
      <a data-slide="prev" role="button" href="#carousel-example-generic" class="left carousel-control"> 
      	<span aria-hidden="true" class="glyphicon glyphicon-chevron-left"></span> 
        <span class="sr-only">上一个</span> 
      </a>
      <a data-slide="next" role="button" href="#carousel-example-generic" class="right carousel-control">
      	<span aria-hidden="true" class="glyphicon glyphicon-chevron-right"></span>
        <span class="sr-only">下一个</span>
      </a>
    </div>
  </div>
</div>
<!--banner end-->
<!--main start-->
<div class="main">
  <div class="container">
	<div class="main-left">
		<!--bar start-->
    	<div class="product-bar">
        	<div class="product-bar-left">
            	全部商品
            </div>
        	<div class="product-bar-right">
				<ul id="myTab" class="nav nav-tabs">
					<li>|</li>
					<?php $__CATEGORY__ = db('GoodsCate')->where("pid=0")->limit(6)->order("id desc")->select();if(is_array($__CATEGORY__) || $__CATEGORY__ instanceof \think\Collection): $i = 0; $__LIST__ = $__CATEGORY__;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$cate): $mod = ($i % 2 );++$i;?>
						<li class="<?php if($key == '0'): ?>active<?php endif; ?>">
							<a href="#tab-<?php echo $key; ?>" data-toggle="tab">
							<?php echo $cate['name']; ?>
							</a>
						</li>
						<li>|</li>
					<?php endforeach; endif; else: echo "" ;endif; ?>
				</ul>
            </div>
        </div>
        <!--bar end-->
        <!--product start-->
        <div class="product-box">
		<div id="myTabContent" class="tab-content">
			<?php $__CATEGORY__ = db('GoodsCate')->where("pid=0")->limit(6)->order("id desc")->select();if(is_array($__CATEGORY__) || $__CATEGORY__ instanceof \think\Collection): $i = 0; $__LIST__ = $__CATEGORY__;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$cate): $mod = ($i % 2 );++$i;?>
   			<div class="tab-pane fade <?php if($key == '0'): ?>in active<?php endif; ?>" id="tab-<?php echo $key; ?>">
			<div class="row">
				<?php echo theme_get_product_list($cate['id']); ?>
            </div>
   			</div>
            <!--分割线-->
			<?php endforeach; endif; else: echo "" ;endif; ?>
		</div>
        </div>
        <!--product end-->
        <!--about us start-->
        <div class="about-box" style="margin-top:0px;">
			<a href="<?php echo url('index/article/page','name=company'); ?>">
        	<div class="about-picture">
            	<!-- <img src="<?php echo config('theme_path'); ?>/index/images/00.png"> -->
            </div>
			</a>
        	<div class="about-text">
				<?php $__PAGES__ = db('posts')->field(true)->where(" id=2")->where('type','page')->order("level")->select();if(is_array($__PAGES__) || $__PAGES__ instanceof \think\Collection): $i = 0; $__LIST__ = $__PAGES__;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$page): $mod = ($i % 2 );++$i;?>
				<a href="<?php echo url('index/article/page','name=company'); ?>">
					<div class="title"><?php echo $page['title']; ?></div>
					<div style="clear:both;"></div>
					<div class="content">
						<?php echo msubstr(strip_tags($page['content']),0,300); ?>
					</div>
				</a>
				<?php endforeach; endif; else: echo "" ;endif; ?>
                <a id="more" class="more-off" href="<?php echo url('index/article/page','name=company'); ?>"></a>
            </div>
            <div style="clear:both;"></div>
        </div>
        <!--about us end-->
        <!--news start-->
        <div class="news-bar">
        	<div class="news-bar-left">新闻中心</div>
        	<div class="news-bar-right"><a href="<?php echo url('index/article/lists','category=news'); ?>">更多</a></div>
            <div style="clear:both;"></div>
        </div>
        <div class="news-box">
        	<table class="table table-bordered table-no-border">
				<?php $__POSTS__ = db('TermRelationships')->alias('a')->join('posts b','b.id= a.object_id','LEFT')->field('b.*,a.term_taxonomy_id as category')->where('a.term_taxonomy_id','in',"2,3")->where('b.status','publish')->order("id desc")->limit(4)->select();if(is_array($__POSTS__) || $__POSTS__ instanceof \think\Collection): $i = 0; $__LIST__ = $__POSTS__;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$post): $mod = ($i % 2 );++$i;switch($key): case "0": ?>
						<tr>
							<td width="50%">
								<div class="news-picture"><img src="<?php echo get_posts_cover($post['uuid']); ?>" width="135" height="100"></div>
								<div class="news-text">
									<div class="news-title">
									<?php echo $post['title']; ?>
									</div>
									<div class="news-content">
									<?php echo get_val($post['uuid'],'description',30); ?>
									</div>
									<a id="more" class="more-off" href="<?php echo url('index/article/detail',['id'=>$post['id'],'category'=>$post['category']]); ?>"></a>
								</div>
							</td>
						<?php break; case "1": ?>
							<td width="50%">
								<div class="news-picture"><img src="<?php echo get_posts_cover($post['uuid']); ?>" width="135" height="100"></div>
								<div class="news-text">
									<div class="news-title">
									<?php echo $post['title']; ?>
									</div>
									<div class="news-content">
									<?php echo get_val($post['uuid'],'description',30); ?>
									</div>
									<a id="more" class="more-off" href="<?php echo url('index/article/detail',['id'=>$post['id'],'category'=>$post['category']]); ?>"></a>
								</div>
							</td>
						</tr>
						<?php break; case "2": ?>
						<tr>
							<td>
								<div class="news-picture"><img src="<?php echo get_posts_cover($post['uuid']); ?>" width="135" height="100"></div>
								<div class="news-text">
									<div class="news-title">
									<?php echo $post['title']; ?>
									</div>
									<div class="news-content">
									<?php echo get_val($post['uuid'],'description',30); ?>
									</div>
									<a id="more" class="more-off" href="<?php echo url('index/article/detail',['id'=>$post['id'],'category'=>$post['category']]); ?>"></a>
								</div>
							</td>
						<?php break; case "3": ?>
							<td>
								<div class="news-picture"><img src="<?php echo get_posts_cover($post['uuid']); ?>" width="135" height="100"></div>
								<div class="news-text">
									<div class="news-title">
									<?php echo $post['title']; ?>
									</div>
									<div class="news-content">
									<?php echo get_val($post['uuid'],'description',30); ?>
									</div>
									<a id="more" class="more-off" href="<?php echo url('index/article/detail',['id'=>$post['id'],'category'=>$post['category']]); ?>"></a>
								</div>
							</td>
						</tr>
						<?php break; default: ?>无数据
					<?php endswitch; endforeach; endif; else: echo "" ;endif; ?>
            </table>
        </div>
        <!--news end-->
    </div>
	<div class="main-right">
    	<div class="mr-bar-box">
        	<table class="table table-bordered" style="background-color:#fff; text-align:center;">
            	<tr>
                	<td width="50%" height="100">
                    	<a href="<?php echo url('index/article/page','name=honor'); ?>" style="text-decoration:none;">
                    	<div class="mr-bar-picture" style="margin-top:10px;">
                    		<img src="<?php echo config('theme_path'); ?>/index/images/icon_01.png">
                        </div>
                    	<div class="mr-bar-title" style="margin-top:5px; color:#cccccc;">
                    		资质荣誉
                        </div>
                        </a>
                    </td>
                    <td width="50%" height="100">
                    	<a href="<?php echo url('index/article/page','name=culture'); ?>" style="text-decoration:none;">
                    	<div class="mr-bar-picture" style="margin-top:10px;">
                    		<img src="<?php echo config('theme_path'); ?>/index/images/icon_02.png">
                        </div>
                    	<div class="mr-bar-title" style="margin-top:5px; color:#cccccc;">
                    		企业文化
                        </div>
                        </a>
                    </td>
                </tr>
            	<tr>
                	<td height="100">
                    	<a href="<?php echo url('index/article/page','name=history'); ?>" style="text-decoration:none;">
                    	<div class="mr-bar-picture" style="margin-top:10px;">
                    		<img src="<?php echo config('theme_path'); ?>/index/images/icon_03.png">
                        </div>
                    	<div class="mr-bar-title" style="margin-top:5px; color:#cccccc;">
                    		发展历程
                        </div>
                        </a>
                    </td>
                    <td height="100">
                        <a href="<?php echo url('index/article/page','name=company'); ?>" style="text-decoration:none;">
                    	<div class="mr-bar-picture" style="margin-top:10px;">
                    		<img src="<?php echo config('theme_path'); ?>/index/images/icon_04.png">
                        </div>
                    	<div class="mr-bar-title" style="margin-top:5px; color:#cccccc;">
                    		企业简介
                        </div>
                        </a>
                    </td>
                </tr>
            </table>
        </div>
        
        <!--行业资讯 开始-->
        <div class="hyzx-bar-box">
        	<div class="hbb-left">行业资讯</div>
        	<div class="hbb-right"><a href="<?php echo url('index/article/lists','category=info'); ?>">更多</a></div>
        </div>
        <div class="hyzx-list-box">
        	<ul>
				<?php $__POSTS__ = db('TermRelationships')->alias('a')->join('posts b','b.id= a.object_id','LEFT')->field('b.*,a.term_taxonomy_id as category')->where('a.term_taxonomy_id','in',3)->where('b.status','publish')->order("id desc")->limit(10)->select();if(is_array($__POSTS__) || $__POSTS__ instanceof \think\Collection): $i = 0; $__LIST__ = $__POSTS__;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$post): $mod = ($i % 2 );++$i;?>
            	<li><a href="<?php echo url('index/article/detail',['id'=>$post['id'],'category'=>$post['category']]); ?>"><?php echo $post['title']; ?></a></li>
				<?php endforeach; endif; else: echo "" ;endif; ?>
            </ul>
        </div>
        <!--行业资讯 结束-->
        <!--热销商品 开始-->
        <div class="hot-product">
		<ul id="myTab" class="nav nav-tabs">
   			<li class="active">
      			<a href="#hot" data-toggle="tab">
         		热销商品
      			</a>
   			</li>
            <li style="border-left:1px #999999 solid;float:left; width:2%; height:18px; margin-top:16px;"> &nbsp;</li>
   			<li>
            	<a href="#good" data-toggle="tab">
            	精品排名
            	</a>
            </li>
		</ul>
		<div id="myTabContent" class="tab-content">
   			<div class="tab-pane fade in active" id="hot">
				<?php $__GOODS__ = db('GoodsCateRelationships')->alias('a')->join('goods b','b.id= a.goods_id','LEFT')->field('b.*,a.cate_id as category')->where('b.status','1')->where('b.is_hot','1')->order("sell_num desc,id desc")->limit(4)->select();if(is_array($__GOODS__) || $__GOODS__ instanceof \think\Collection): $i = 0; $__LIST__ = $__GOODS__;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$goods): $mod = ($i % 2 );++$i;?>
					<!--box start-->
					<div class="hpp-box">
						<div class="hpp-box-left">
							<div class="hppbl-title">
								<a href="<?php echo url('index/goods/detail','id='.$goods['id']); ?>"><?php echo $goods['name']; ?></a>
							</div>
							<div class="hppbl-hot">
								<img src="<?php echo config('theme_path'); ?>/index/images/icon_heat.png">
								销售热度：<?php echo $goods['sell_num']; ?>
							</div>
						</div>
						<div class="hpp-box-right">
							<img src="<?php echo root_path(); ?><?php echo $goods['cover_path']; ?>" height="60" width="85">
						</div>
						<div style="clear:both;"></div>
					</div>
					<!--box end-->
				<?php endforeach; endif; else: echo "" ;endif; ?>
                <div class="hpp-box-more">
                	<a href="<?php echo url('index/goods/lists','category=cabbage'); ?>">查看更多>></a>
                </div>
   			</div>
   			<div class="tab-pane fade" id="good">
				<?php $__GOODS__ = db('GoodsCateRelationships')->alias('a')->join('goods b','b.id= a.goods_id','LEFT')->field('b.*,a.cate_id as category')->where('b.status','1')->where('b.is_best','1')->order("sell_num desc,id desc")->limit(4)->select();if(is_array($__GOODS__) || $__GOODS__ instanceof \think\Collection): $i = 0; $__LIST__ = $__GOODS__;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$goods): $mod = ($i % 2 );++$i;?>
					<!--box start-->
					<div class="hpp-box">
						<div class="hpp-box-left">
							<div class="hppbl-title">
								<a href="<?php echo url('index/goods/detail','id='.$goods['id']); ?>"><?php echo $goods['name']; ?></a>
							</div>
							<div class="hppbl-hot">
								<img src="<?php echo config('theme_path'); ?>/index/images/icon_heat.png">
								销售热度：<?php echo $goods['sell_num']; ?>
							</div>
						</div>
						<div class="hpp-box-right">
							<img src="<?php echo root_path(); ?><?php echo $goods['cover_path']; ?>" height="60" width="85">
						</div>
						<div style="clear:both;"></div>
					</div>
					<!--box end-->
				<?php endforeach; endif; else: echo "" ;endif; ?>
                <div class="hpp-box-more">
                	<a href="<?php echo url('index/goods/lists','category=cabbage'); ?>">查看更多>></a>
                </div>
   			</div>
		</div>
        </div>
        <!--热销商品 结束-->
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
<!--friendlink start-->
<div class="friendlink">
	<div class="container">
		<div class="friendlink-bar">
    		<div class="friendlink-bar-left">友情链接</div>
    		<div class="friendlink-bar-right">
                <?php $__LINK__ = db('links')->field(true)->where("visible","Y")->order("createtime")->select();if(is_array($__LINK__) || $__LINK__ instanceof \think\Collection): $i = 0; $__LIST__ = $__LINK__;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$data): $mod = ($i % 2 );++$i;?>
                    <a href="<?php echo $data['url']; ?>"  target="<?php echo $data['target']; ?>"><?php echo $data['name']; ?></a>&nbsp; | &nbsp;
                <?php endforeach; endif; else: echo "" ;endif; ?>
            </div>
    	</div>
    </div>
</div>
<!--friendlink end-->
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
    $("div #more").hover(function(){
		$(this).removeClass("more-off");
		$(this).addClass("more-on");
	},function(){
		$(this).removeClass("more-on");
		$(this).addClass("more-off");
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
	$(".img").mouseover(function(e) {
        $(this).stop().fadeTo(500,0.65);
    });
	$('.img').mouseout(function(e) {
        $(this).stop().fadeTo(500,1);
    });
});
</script>

</body>
</html>