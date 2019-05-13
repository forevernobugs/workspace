<?php if (!defined('THINK_PATH')) exit(); /*a:5:{s:75:"C:\phpStudy\PHPTutorial\WWW\shop/application/admin\view\comment\detail.html";i:1473430978;s:72:"C:\phpStudy\PHPTutorial\WWW\shop/application/admin\view\common\head.html";i:1556075560;s:74:"C:\phpStudy\PHPTutorial\WWW\shop/application/admin\view\common\header.html";i:1555420220;s:74:"C:\phpStudy\PHPTutorial\WWW\shop/application/admin\view\common\navbar.html";i:1471616304;s:74:"C:\phpStudy\PHPTutorial\WWW\shop/application/admin\view\common\footer.html";i:1556075601;}*/ ?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>服装后台系统</title>
  <!-- Tell the browser to be responsive to screen width -->
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
  <!-- Bootstrap 3.3.6 -->
  <link rel="stylesheet" href="STATIC_PATH/bootstrap/css/bootstrap.min.css">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdn.bootcss.com/font-awesome/4.6.3/css/font-awesome.min.css">
  <!-- Ionicons -->
  <link rel="stylesheet" href="https://cdn.bootcss.com/ionicons/2.0.1/css/ionicons.min.css">
  <!-- Theme style -->
  <link rel="stylesheet" href="STATIC_PATH/dist/css/AdminLTE.min.css">
  <!-- AdminLTE Skins. Choose a skin from the css/skins
       folder instead of downloading all of them to reduce the load. -->
  <link rel="stylesheet" href="STATIC_PATH/dist/css/skins/_all-skins.min.css">

  <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
  <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
  <!--[if lt IE 9]>
  <script src="https://cdn.bootcss.com/html5shiv/3.7.3/html5shiv.min.js"></script>
  <script src="https://cdn.bootcss.com/respond.js/1.4.2/respond.min.js"></script>
  <![endif]-->
</head>
<link rel="stylesheet" type="text/css" href="STATIC_PATH/plugins/webuploader/css/webuploader.css" />
<link rel="stylesheet" type="text/css" href="STATIC_PATH/plugins/webuploader/examples/image-upload/style.css" />
<script src="STATIC_PATH/plugins/jQuery/jquery-1.9.1.min.js"></script>
<body class="skin-blue sidebar-mini wysihtml5-supported fixed">
<div class="wrapper">
  <style type="text/css">
    label {
    display: inline-block;
    font-weight: 700;
    margin-bottom: 5px;
    max-width: 100%;
    font-size: 12px;
    }
  </style>
 <header class="main-header">
    <!-- Logo -->
    <a href="<?php echo url('Index/index'); ?>" class="logo">
      <!-- mini logo for sidebar mini 50x50 pixels -->
      <span class="logo-mini"><b>M</b>S</span>
      <!-- logo for regular state and mobile devices -->
      <span class="logo-lg"><b>服装后台系统</b></span>
    </a>
    <!-- Header Navbar: style can be found in header.less -->
    <nav class="navbar navbar-static-top">
      <!-- Sidebar toggle button-->
      <a href="#" class="sidebar-toggle" data-toggle="offcanvas" role="button">
        <span class="sr-only">Toggle navigation</span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
      </a>

      <div class="navbar-custom-menu">
        <ul class="nav navbar-nav">
          <!-- Messages: style can be found in dropdown.less-->
         <!--  <li class="dropdown messages-menu">
            <a href="#" class="dropdown-toggle" data-toggle="dropdown">
              <i class="fa fa-envelope-o"></i>
              <span class="label label-success">4</span>
            </a>
           
          </li> -->
          <!-- Notifications: style can be found in dropdown.less -->
          <li class="dropdown notifications-menu">
            <!-- <a href="#" class="dropdown-toggle" data-toggle="dropdown">
              <i class="fa fa-bell-o"></i>
              <span class="label label-warning">10</span>
            </a>
            <ul class="dropdown-menu">
              <li class="header">You have 10 notifications</li>
             
              <li class="footer"><a href="#">View all</a></li>
            </ul> -->
          </li>
          <!-- Tasks: style can be found in dropdown.less -->
          <li class="dropdown tasks-menu">
            <!-- <a href="#" class="dropdown-toggle" data-toggle="dropdown">
              <i class="fa fa-flag-o"></i>
              <span class="label label-danger">9</span>
            </a>
            <ul class="dropdown-menu">
              <li class="header">You have 9 tasks</li>
              
              <li class="footer">
                <a href="#">View all tasks</a>
              </li>
            </ul> -->
          </li>
          <!-- User Account: style can be found in dropdown.less -->
          <li class="dropdown user user-menu">
            <a href="#" class="dropdown-toggle" data-toggle="dropdown">
             
              <span class="hidden-xs"><?php echo Session('admin_user_auth.username'); ?></span>
            </a>
            <ul class="dropdown-menu">
 
              <li class="user-footer">
                <div class="pull-right">
                  <a href="<?php echo url('user/edit'); ?>" class="btn btn-default btn-flat">个人资料</a>
                  
                </div>
                </li>
                <li>
                 <div class="box-footer">
                  
                   <a href="<?php echo url('common/logout'); ?>" class="btn btn-default btn-flat">退出</a>
                </div>
                
              </li>
            </ul>
          </li>
          <!-- Control Sidebar Toggle Button -->
         <!--  <li>
            <a href="#" data-toggle="control-sidebar"><i class="fa fa-gears"></i></a>
          </li> -->
        </ul>
      </div>
    </nav>
  </header>
<!-- Left side column. contains the logo and sidebar -->
<aside class="main-sidebar">
    <!-- sidebar: style can be found in sidebar.less -->
    <section class="sidebar">
      <!-- Sidebar user panel -->
      <div class="user-panel" style="height:40px;">
        <div class="pull-left info">
          <p><?php echo Session('admin_user_auth.username'); ?> <a href="#"><i class="fa fa-circle text-success"></i> </a></p>
        </div>
      </div>
      <!-- sidebar menu: : style can be found in sidebar.less -->
      <ul class="sidebar-menu">
        <li class="header">导航</li>
        <?php if(is_array($menuTree) || $menuTree instanceof \think\Collection): $i = 0; $__LIST__ = $menuTree;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?>
        <li class="<?php echo get_menu_navbar_active($vo['id']); ?> treeview">
          <a href="<?php echo $vo['url']; ?>">
            <i class="<?php echo $vo['icon']; ?>"></i> <span><?php echo $vo['name']; ?></span> <i class="fa fa-angle-left pull-right"></i>
          </a>
          <?php if(!(empty($vo['_child']) || ($vo['_child'] instanceof \think\Collection && $vo['_child']->isEmpty()))): ?>
          <ul class="treeview-menu">
            <?php if(is_array($vo['_child']) || $vo['_child'] instanceof \think\Collection): $i = 0; $__LIST__ = $vo['_child'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$sub): $mod = ($i % 2 );++$i;?>
                <li class="<?php echo get_menu_navbar_active($sub['id']); ?>"><a href="<?php echo url($sub['url']); ?>"><i class="<?php echo $sub['icon']; ?>"></i><?php echo $sub['name']; ?></a></li>
            <?php endforeach; endif; else: echo "" ;endif; ?>
          </ul>
          <?php endif; ?>
        </li>
        <?php endforeach; endif; else: echo "" ;endif; ?>
      </ul>
    </section>
    <!-- /.sidebar -->
</aside>
  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>
       评论管理
        <small>审核评论</small>
      </h1>
      <ol class="breadcrumb">
        <li><a href="<?php echo url('admin/index/index'); ?>"><i class="fa fa-dashboard"></i> 主页</a></li>
        <li><a href="<?php echo url('admin/comment/index'); ?>">评论管理</a></li>
        <li class="active"><a href="<?php echo url('admin/comment/detail'); ?>">审核评论</a></li>
      </ol>
    </section>
 
    <!-- Main content -->
    <section class="content">
      <div class="row">
        <div class="col-md-12">
          <div class="box box-primary">
            <div class="box-header with-border">
              <h3 class="box-title">审核评论</h3>
              
            </div>
            <div class="box-body no-padding">
              <form method="post"  action="<?php echo url('admin/comment/edit'); ?>" id="comment">
                <div class="box-body">
                    <input type="hidden" id="comment_id" class="" value="<?php echo $commentInfo['id']; ?>" />
                    <div class="form-group">
                    <label for="link_name" id="uname_label" class="">
                      操作用户
                    </label>
                    <input class="form-control" id="comment_uid" name="comment_uid" value="<?php echo get_userinfo($commentInfo['uid'],'username'); ?>" placeholder="操作用户" type="text" readonly="text">
                    <input name="id" hidden="hidden" value="<?php echo get_userinfo($commentInfo['uid'],'username'); ?>">
                  </div>
                  <div class="form-group">
                    <label for="link_name" id="uname_label" class="">对应文章id</label>
                    <input class="form-control" id="comment_name" name="comment_name" value="<?php echo $commentInfo['id']; ?>" placeholder="对应文章id" type="text" readonly="text">
                    <input name="id" hidden="hidden" value="<?php echo $commentInfo['id']; ?>">
                  </div>
                  <div class="form-group">
                    <label for="comment_order" class="">订单号</label>
                    <input class="form-control" id="comment_order" name="comment_order" value="<?php echo $commentInfo['order_id']; ?>" placeholder="订单号" type="text" readonly="text">
                  </div> 
                  <div class="form-group">
                    <label for="comment_order" class="">评论内容</label>

                    <textarea class="form-control" id="comment_order" name="comment_order" value="" placeholder="评论内容" type="text" readonly="text" style="height:100px;width:100%;" ><?php echo $commentInfo['content']; ?></textarea>
                  </div>
                  <div class="form-group">
                    <label for="comment_createtime" class="">评论时间</label>
                    <input class="form-control" id="comment_createtime" name="comment_createtime" value="<?php echo date('Y-m-d H:i:s',$commentInfo['createtime']); ?>" placeholder="评论时间" type="text/plain" readonly="time">
                  </div>                 
                  <div class="form-group">
                    <label for="target" class="">审核状态</label>
                    <select class="form-control" id="approved" name="approved">
                      <?php if($commentInfo['approved'] == '0'): ?>
                        <option value="0" selected="selected">待审核</option>
                        <option value="1">已审核</option> 
                      <?php else: ?>
                        <option value="0" >待审核</option>
                        <option value="1" selected="selected">已审核</option>  
                      <?php endif; ?>  
                    </select>
                  </div>
                  <div class="form-group">
                    <label for="description" class="">商品评分</label>
                    <input class="form-control" name="description" id="description" value="<?php echo $commentInfo['score']; ?>分" placeholder="商品评分" type="text" readonly="text">
                  </div>
                  <div class="form-group">
                    <label for="visible" class="">显示</label>
                    <select class="form-control" id="visible" name="visible">
                      <?php if($commentInfo['status'] == '1'): ?>
                        <option value="-1" >删除</option>
                        <option value="1"  selected="selected">正常</option> 
                      <?php else: ?>
                        <option value="-1" selected="selected">删除</option>
                        <option value="1" >正常</option>   
                      <?php endif; ?>           
                    </select>
                  </div>                                    
                  <div class="pull-right">
                    <button type="button" class="btn btn-primary submit">确定</button>
                  </div>
                </div>
              </form>

<script type="text/javascript"> 
    //修改审核、状态
    $('.submit').click(function(){
      var id       = $("#comment_id").val();
      var approved = $("#approved").val();
      var visible  = $("#visible").val();
      $.ajax({
        cache:true,
        type :"POST",
        url  :'<?php echo url('comment/edit'); ?>',
        data :{
          "approved"  :approved,
          "visible"   :visible,
          "id"        :id
        },
        async:false,
           success:function(data){
            if(data.code){
              alert(data.msg);
              setTimeout(function(){
                location.href=data.url;
              },1000);
            } else {
              alert(data.msg);
            }
           },
           error:function(request){
            alert("页面错误");
           }
      });        
    });

</script>
             
            </div>
            <!-- /.box-body -->
          </div>
          <!-- /. box -->
        </div>
        <!-- /.col -->
      </div>
      <!-- /.row -->
    </section>
    <!-- /.content -->
  </div>
  <!-- /.content-wrapper -->
  
  <footer class="main-footer">
    <div class="pull-right hidden-xs">
      <b>帆帆</b> 1.0.0
    </div>
    <strong>Copyright &copy; 2018-2019 <a href="http://qasl.cn">帆帆网站</a>.</strong> All rights
    reserved.
  </footer>
  <script type="text/javascript" src="http://tajs.qq.com/stats?sId=58696658" charset="UTF-8"></script>
</div>
<!-- ./wrapper -->
<script type="text/javascript">
  var uploadPictuer     = '<?php echo url('Upload/uploadPicture'); ?>';  
</script>

<script type="text/javascript" src="STATIC_PATH/plugins/webuploader/dist/webuploader.js"></script>
<script type="text/javascript" src="STATIC_PATH/plugins/webuploader/examples/image-upload/upload.js"></script>
<!-- jQuery 2.2.0 -->

<script src="STATIC_PATH/plugins/jQueryUI/jquery-ui.min.js"></script>
<script type="text/javascript">
  $('document').ready(function (argument) {
    $('#addCategoryFormSubmit').click(function () {
      $.ajax({
        cache: true,
        type: "POST",
        url : $('#addCategoryForm').attr('action'),
        data: $('#addCategoryForm').serialize(),
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

    });

    $('.insert_images').on('click',function () {
      var list = new Array(); //定义一数组
      list = $('#img_list').val().split(","); //字符分割
      //下面使用each进行遍历
      var text = '';
      $.each(list,function(n,value) {
        if (value !== null && value !== undefined && value !== '') {
          text = text+"<div class='form-group'><img class='cover_path' style='max-height:150px;' src='ROOT_PATH"+value+"'></div>";
          $('#img_list').val(value);
        }
      });
      $('.cover_show').empty();
      $('.cover_show').html(text);
      uploader = "<div class='queueList'><div id='dndArea' class='placeholder'><div id='filePicker'></div></div></div><div class='statusBar' style='display:none;'><div class='progress' style='position:relative;'><span class='text'>0%</span><span class='percentage'></span></div><div class='info'></div><div class='btns'><div class='uploadBtn'>开始上传</div></div></div>";
      // 重置uploader
      $('#uploader').html(uploader);
      // 隐藏Modal
      $('#myModal').modal('hide');
    });
  });
</script>
<!-- Resolve conflict in jQuery UI tooltip with Bootstrap tooltip -->
<script>
  $.widget.bridge('uibutton', $.ui.button);
</script>
<!-- Bootstrap 3.3.6 -->
<script src="STATIC_PATH/bootstrap/js/bootstrap.min.js"></script>
<!-- Slimscroll -->
<script src="STATIC_PATH/plugins/slimScroll/jquery.slimscroll.min.js"></script>
<!-- AdminLTE App -->
<script src="STATIC_PATH/dist/js/app.min.js"></script>
</body>
</html>










