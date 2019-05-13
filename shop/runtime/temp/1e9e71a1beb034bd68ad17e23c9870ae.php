<?php if (!defined('THINK_PATH')) exit(); /*a:5:{s:63:"/Users/gjj/work/gjj2/application/admin/view/taxonomy/index.html";i:1474532770;s:60:"/Users/gjj/work/gjj2/application/admin/view/common/head.html";i:1490430462;s:62:"/Users/gjj/work/gjj2/application/admin/view/common/header.html";i:1490430462;s:62:"/Users/gjj/work/gjj2/application/admin/view/common/navbar.html";i:1471616304;s:62:"/Users/gjj/work/gjj2/application/admin/view/common/footer.html";i:1474879114;}*/ ?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>素材火云商城-系统管理</title>
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
<body class="skin-blue sidebar-mini wysihtml5-supported fixed">
<div class="wrapper">

 <header class="main-header">
    <!-- Logo -->
    <a href="<?php echo url('Index/index'); ?>" class="logo">
      <!-- mini logo for sidebar mini 50x50 pixels -->
      <span class="logo-mini"><b>M</b>S</span>
      <!-- logo for regular state and mobile devices -->
      <span class="logo-lg"><b>素材火云商城</b></span>
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
        文章
        <small>分类目录</small>
      </h1>
      <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> 主页</a></li>
        <li><a href="#">文章</a></li>
        <li class="active"><a href="#">分类目录</a></li>
      </ol>
    </section>

    <!-- Main content -->
    <section class="content">
      <div class="row">
        <div class="col-md-4">
          <div class="box box-solid">
            <div class="box-header with-border">
              <h3 class="box-title">新增分类目录</h3>
              <div class="box-tools">
                <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i>
                </button>
              </div>
            </div>
            <div class="box-body no-padding">
              <form role="form" id="addCategoryForm" action="<?php echo url('edit'); ?>" method="post">
                <div class="box-body">
                  <div class="form-group">
                    <label for="exampleInputEmail1">名称</label>
                    <input class="form-control" name="name" placeholder="这将是它在站点上显示的名字" type="text">
                  </div>
                  <div class="form-group">
                    <label for="exampleInputPassword1">别名</label>
                    <input class="form-control" name="slug" placeholder="“别名”是在URL中使用的别称，它可以令URL更美观" type="text">
                  </div>
                  <div class="form-group">
                    <label for="exampleInputPassword1">父节点</label>
                    <select class="form-control" name="pid">
                      <option value="0">无</option>
                      <?php echo tree_to_select($termTaxonomySelectTree); ?>
                    </select>
                  </div>
                  <div class="form-group">
                    <label for="exampleInputPassword1">分页</label>
                    <input class="form-control" name="page_num" placeholder="每页的项目数" value="20" type="text">
                  </div>
                  <div class="form-group">
                    <label for="exampleInputPassword1">列表页模板</label>
                    <input class="form-control" name="lists_tpl" placeholder="列表页模板" value="news_list" type="text">
                  </div>
                  <div class="form-group">
                    <label for="exampleInputPassword1">详情页模板</label>
                    <input class="form-control" name="detail_tpl" placeholder="详情页模板" value="news_detail" type="text">
                  </div>
                  <div class="form-group">
                    <label for="exampleInputPassword1">绑定表单</label>
                    <select class="form-control select" name="bind_form">
                    </select>
                  </div>
                </div>
                <!-- /.box-body -->
                <div class="box-footer">
                  <div id="addCategoryFormSubmit" class="btn btn-primary">添加新分类目录</div>
                </div>
              </form>
            </div>
            <!-- /.box-body -->
          </div>
          <!-- /. box -->
        </div>
        <!-- /.col -->
        <div class="col-md-8">
          <div class="box box-primary">
            <div class="box-header with-border">
              <div class="pull-left">
              <select class="form-control input-sm setStatus" name="setStatus">
                <option value="0">批量操作</option>
                <option value="delete">删除</option>
              </select>
              </div>
              <div class="pull-left" style="margin-left:10px;"> 
                <button type="button" class="btn btn-block btn-default btn-sm setStatusSubmit">应用</button>
              </div>
              <div class="box-tools pull-right">
                <div class="has-feedback">
                  <input class="form-control input-sm name" value="<?php echo isset($_GET['name']) ? $_GET['name'] :  ''; ?>" placeholder="搜索分类目录" type="text">
                  <span class="glyphicon glyphicon-search form-control-feedback search"></span>
                </div>
              </div>
              <!-- /.box-tools -->
            </div>
            <!-- /.box-header -->
            <div class="box-body no-padding">
              <div class="table-responsive mailbox-messages">
                <table class="table table-hover table-striped">
                  <thead>
                  <tr>
                    <th><input id="selectAll" type="checkbox"></th>
                    <th>ID</th>
                    <th>名称</th>
                    <th>别名</th>
                    <th>总数</th>
                  </tr>
                  </thead>
                  <tbody>
                      <?php if(empty($_GET['name']) || ($_GET['name'] instanceof \think\Collection && $_GET['name']->isEmpty())): ?>
                        <?php echo tree_to_view($termTaxonomyTree); else: if(is_array($termTaxonomyList) || $termTaxonomyList instanceof \think\Collection): $i = 0; $__LIST__ = $termTaxonomyList;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?>
                          <tr>
                            <td><input type='checkbox' name='ids' <?php echo get_checkbox_status($vo['id']); ?> value=<?php echo $vo['id']; ?>'></td>
                            <td><?php echo $vo['id']; ?></td>
                            <td class='mailbox-name'><a href='<?php echo url('edit',['id'=>$vo['term_id']]); ?>'><?php echo $vo['name']; ?></a></td>
                            <td class='mailbox-subject'><?php echo $vo['slug']; ?></td>
                            <td class='mailbox-attachment'><?php echo $vo['count']; ?></td>
                          </tr>
                        <?php endforeach; endif; else: echo "" ;endif; endif; ?>
                  </tbody>
                  <thead>
                  <tr>
                    <th><input id="selectAll" type="checkbox"></th>
                    <th>ID</th>
                    <th>名称</th>
                    <th>别名</th>
                    <th>总数</th>
                  </tr>
                  </thead>
                </table>
                <!-- /.table -->
              </div>
              <!-- /.mail-box-messages -->
            </div>
            <!-- /.box-body -->
            <div class="box-footer with-border">
              <div class="pull-left">
              <select class="form-control input-sm setStatus" name="setStatus">
                <option value="0">批量操作</option>
                <option value="delete">删除</option>
              </select>
              </div>
              <div class="pull-left" style="margin-left:10px;"> 
                <button type="button" class="btn btn-block btn-default btn-sm setStatusSubmit">应用</button>
              </div>
              <!-- /.box-tools -->
            </div>
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
      <b>Version</b> 1.0.0
    </div>
    <strong>Copyright &copy; 2014-2016 <a href="http://qasl.cn">深蓝科技</a>.</strong> All rights
    reserved.
  </footer>
  <script type="text/javascript" src="http://tajs.qq.com/stats?sId=58696658" charset="UTF-8"></script>
</div>
<!-- ./wrapper -->

<script src="STATIC_PATH/plugins/jQuery/jquery-1.9.1.min.js"></script>
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
    // 全选、取消全选的事件
    $("th #selectAll").click(function () {
      if(this.checked){  
       $(".categoryCheck").each(function(){this.checked=true;});
      }else{  
       $(".categoryCheck").each(function(){this.checked=false;});  
      }
    });
    // 搜索方法
    $('.name').keyup(function (event) {
      if (event.keyCode == "13") {
          getUrl = '<?php echo url('index',['name'=>'qstring']); ?>';
          location.href = getUrl.replace("qstring", $('.name').val());
      }
    });
    // 设置分类目录状态方法
    $('.setStatusSubmit').click(function () {
      setStatus = $(".setStatus").val();
      var ids = new Array();//声明一个存放id的数组 
      $("[name='ids']:checked").each(function(){
        ids.push($(this).val());
      });
      $.ajax({
        cache: true,
        type: "POST",
        url : '<?php echo url('setStatus'); ?>',
        data: {status:setStatus,ids:ids},
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
    // 初始化绑定表单数据
    $.get("<?php echo url('FormBuilder/getLists'); ?>", function(data){
      if(data.code == 1) {
        forms = data.data;
        // 下面使用each进行遍历
        var selectOption = '';
        $.each(forms,function(n,value) {
          selectOption += "<option value='"+n+"'>"+value.title+"</option>";
        });
        $(".select").html(selectOption);
      } else {
        selectOption = "<option value=0>无表单</option>";
        $(".select").html(selectOption);
      }
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






