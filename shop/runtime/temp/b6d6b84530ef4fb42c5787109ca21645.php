<?php if (!defined('THINK_PATH')) exit(); /*a:5:{s:76:"C:\phpStudy\PHPTutorial\WWW\shop/application/admin\view\database\export.html";i:1471616306;s:72:"C:\phpStudy\PHPTutorial\WWW\shop/application/admin\view\common\head.html";i:1490430462;s:74:"C:\phpStudy\PHPTutorial\WWW\shop/application/admin\view\common\header.html";i:1555420220;s:74:"C:\phpStudy\PHPTutorial\WWW\shop/application/admin\view\common\navbar.html";i:1471616304;s:74:"C:\phpStudy\PHPTutorial\WWW\shop/application/admin\view\common\footer.html";i:1474879114;}*/ ?>
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
        数据库
        <small>数据库备份</small>
      </h1>
      <ol class="breadcrumb">
        <li><a href="<?php echo url('Index/index'); ?>"><i class="fa fa-dashboard"></i> 主页</a></li>
        <li><a href="<?php echo url('admin/member/index'); ?>">数据库</a></li>
        <li><a>数据库备份</a></li>
        
      </ol>
    </section>

    <!-- Main content -->
    <section class="content">
      <div class="row">
        <div class="col-md-12">
          <div class="box box-primary">
            <div class="box-header with-border">
               <div class="pull-left">
              <select class="form-control input-sm setStatus" name="status">
                <option value="0">批量操作</option>
                <option value="1">数据表优化</option>
                <option value="2">数据表修复</option>
                <option value="3">数据表备份</option>
              </select>

              </div>
              <div class="pull-left" style="margin-left:10px;"> 
                <button type="button"  class="btn btn-block btn-default btn-sm setStatusSubmit">应用</button>
              </div>
              
              <!-- /.box-tools -->
            </div>
            <!-- /.box-header -->
            <div class="box-body no-padding">
            
              <div class="table-responsive mailbox-messages">
                <table class="table table-hover table-striped">
                  <thead>
                  <tr>
                     <th><input type="checkbox" class="selectAll" checked></th>
                     <th>数据表名</th>
                     <th>类型</th>
                     <th>记录数</th>
                     <th>数据</th>
                     <th>创建时间</th>
                     <th>状态</th>       
                  </tr>
                  </thead>
                  <tbody>
                  <?php if(is_array($list) || $list instanceof \think\Collection): $k = 0; $__LIST__ = $list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$table): $mod = ($k % 2 );++$k;?>
                  <tr>
                    <td ><input type="checkbox" class="check" name="ids" value="<?php echo $table['name']; ?>" checked /></td>
                    <td><?php echo $table['name']; ?></td>
                    <td><?php echo $table['engine']; ?></td>
                    <td><?php echo $table['rows']; ?></td>
                    <td><?php echo format_bytes($table['data_length']); ?></td>
                    <td><?php echo $table['create_time']; ?></td>
                    <td class="bk_status">未备份</td>
                    
                   
                   
                  </tr>
                  <?php endforeach; endif; else: echo "" ;endif; ?>
                  
                  </tbody>
                  <thead>
                  
                  </thead>
                  
                </table>
                <!-- /.table -->
              </div>
              <!-- /.mail-box-messages -->
            </div>
            <!-- /.box-body -->


            <div class="box-footer with-border">
              
             
               <div class="pull-right">
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
<script src="STATIC_PATH/plugins/jQuery/jquery-1.9.1.min.js"></script>
<script src="STATIC_PATH/plugins/jQueryUI/jquery-ui.min.js"></script>
<script src="STATIC_PATH/plugins/layer/layer.js"></script>
<script type="text/javascript">
  $.widget.bridge('uibutton',$.ui.button);
</script>

<script type="text/javascript">
var url;
var tables;
var index;
  $('document').ready(function(argument){
    //全选、取消全选的事件
    $("th .selectAll").click(function(){
      if(this.checked){
        $(".check").each(function(){this.checked=true;});
      }else{
        $(".check").each(function(){this.checked=false;});
      }
    });
    //设置状态方法
    $('.setStatusSubmit').click(function(){
      setStatus = $(".setStatus").val();
      var ids = new Array();//声明一个存放id的数组
      $("[name='ids']:checked").each(function(){
        ids.push($(this).val());
      });
      if(setStatus==0){
        alert('请选择操作类型');
        return;
      }
      if(ids.length==0){
        alert('请选择要操作的数据');
        return;
      }
      if(setStatus == 3){ //备份
          url='<?php echo url('export'); ?>';
          sendbk(url,ids);
      }else{ //表优化、修复
          index = layer.load(1, {
            offset: ['50%', '50%'],
            shade: [0.1,'#fff'] //0.1透明度的白色背景
          });
          if(setStatus==1){ //表优化
            url = '<?php echo url('optimize'); ?>';
          }else{ //表修复
            url = '<?php echo url('repair'); ?>';
          }
          $.ajax({
          cache:true,
          type :"POST",
          url  :url,
          data :{tables:ids},
          async:false,
             success:function(data){
              layer.close(index);
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
      }
           
    });

     // select选中
    $(".filterStatus").val("<?php echo isset($_GET['status']) ? $_GET['status'] :  '0'; ?>");

        function sendbk(url,ids){

            $('.setStatusSubmit').attr("disabled","disabled");
            $('.setStatusSubmit').html("正在发送备份请求...");
            $.post(
                url,
                {tables:ids},
                function(data){
                    if(data.code){
                        tables = data.data.tables;
                        $('.setStatusSubmit').html(data.msg + "开始备份，请不要关闭本页面！");
                        backup(data.data.tab);
                        window.onbeforeunload = function(){ return "正在备份数据库，请不要关闭！" }
                    } else {
                        updateAlert(data.msg,'alert-error');
                        $('.setStatusSubmit').attr("disabled",false);
                        $('.setStatusSubmit').html("立即备份");
                    }
                },
                "json"
            );
            return false;
        }

        function backup(tab, status){
            showmsg(tab.id, "开始备份...(0%)");
            $.get(url, tab, function(data){
                if(data.code){

                    
                    if(!$.isPlainObject(data.data.tab)){
                        id = data.data.tid-1;
                        showmsg(id, data.msg);
                        $('.setStatusSubmit').attr("disabled",false);
                        $('.setStatusSubmit').html("备份完成，点击重新备份");
                        window.onbeforeunload = function(){ return null }
                        return;
                    }else{
                      id = data.data.tab.id-1;
                      showmsg(id, data.msg);
                      backup(data.data.tab, data.data.tab.id);
                    }
                   
                } else {
                    updateAlert(data.info,'alert-error');
                    $('.setStatusSubmit').parent().children().removeClass("disabled");
                    $('.setStatusSubmit').html("立即备份");
                    setTimeout(function(){
                      $('#top-alert').find('button').click();
                      $(that).removeClass('disabled').prop('disabled',false);
                  },1500);
                }
            }, "json");

        }

        function showmsg(id, msg){
          // alert(tables[id]);
            $('table').find("input[value=" + tables[id] + "]").closest("tr").find(".bk_status").html(msg);
        }




  })
</script>
<!-- Bootstrap 3.3.6 -->
<script src="STATIC_PATH/bootstrap/js/bootstrap.min.js"></script>
<!-- Slimscroll -->
<script src="STATIC_PATH/plugins/slimScroll/jquery.slimscroll.min.js"></script>
<!-- AdminLTE App -->
<script src="STATIC_PATH/dist/js/app.min.js"></script>
</body>
</html>