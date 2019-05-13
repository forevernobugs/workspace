<?php if (!defined('THINK_PATH')) exit(); /*a:1:{s:59:"/Users/gjj/work/gjj2/application/admin/view/base/login.html";i:1490430462;}*/ ?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>素材火云商</title>
  <!-- Tell the browser to be responsive to screen width -->
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
  <!-- Bootstrap 3.3.6 -->
  <link rel="stylesheet" href="STATIC_PATH/bootstrap/css/bootstrap.min.css">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="STATIC_PATH/bootstrap/css/font-awesome.min.css">
  <!-- Ionicons -->
  <link rel="stylesheet" href="STATIC_PATH/bootstrap/css/ionicons.min.css">
  <!-- Theme style -->
  <link rel="stylesheet" href="STATIC_PATH/dist/css/AdminLTE.min.css">
  <!-- iCheck -->
  <link rel="stylesheet" href="STATIC_PATH/plugins/iCheck/square/blue.css">

  <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
  <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
  <!--[if lt IE 9]>
  <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
  <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
  <![endif]-->
  <style type="text/css">
  .bg{
     background:url(STATIC_PATH/dist/img/wallpaper.jpg);
     filter:"progid:DXImageTransform.Microsoft.AlphaImageLoader(sizingMethod='scale')";
     -moz-background-size:100% 100%;
     background-size:100% 100%;
  }
  </style>
</head>
<body class="hold-transition login-page bg">
<div class="login-box">
  <div class="login-logo">
    <a href="../../index2.html"><b style="color:white">素材火云商 v1.0</b></a>
  </div>
  <!-- /.login-logo -->
  <div class="login-box-body">
    <p class="login-box-msg"></p>

    <form action="<?php echo url('base/login'); ?>" method="post" id="login">
      <div class="form-group has-feedback">
        <input type="username" name="username" id="username"class="form-control" placeholder="用户名">
        <span id="uname" class="glyphicon glyphicon-envelope form-control-feedback"></span>
      </div>
      <div class="form-group has-feedback">
        <input type="password" name="password" id="password" class="form-control" placeholder="密码">
        <span class="glyphicon glyphicon-lock form-control-feedback"></span>
      </div>
      <div class="form-group has-feedback" id="captcha-container">
        <input type="text" name="captcha" class="form-control" style="width:50%;float:left" placeholder="验证码" >
        <img style="width:45%;float:right;height:34px" src="<?php echo url('admin/base/captcha'); ?>" onclick="this.src='<?php echo url('admin/base/captcha'); ?>?rand='+Math.random()" title="点击刷新">  
        <span class="glyphicon  form-control-feedback"></span>
      </div>
      <div class="row">
        <div class="col-xs-8">
        </div>
        <!-- /.col -->
        <div class="col-xs-4" style="padding-top:4px">
          <button type="button" class="btn btn-primary btn-block btn-flat submit">登录</button>
        </div>
        <!-- /.col -->
      </div>
    </form>
  </div>
  <!-- /.login-box-body -->
</div>
<!-- /.login-box -->
<script src="STATIC_PATH/plugins/jQuery/jquery-1.9.1.min.js"></script>
<script>
  $(function () {
    // 登录
    $('.submit').click(function () {
        $.ajax({
          cache: true,
          type: "POST",
          url : '<?php echo url('base/login'); ?>',
          data: $('#login').serialize(),
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

    $("body").keydown(function(event) {
      if (event.keyCode =="13"){
        $.ajax({
          cache: true,
          type: "POST",
          url : '<?php echo url('base/login'); ?>',
          data: $('#login').serialize(),
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
      }
    })

    var captcha_img = $('#captcha-container').find('img');
    var verifyimg = captcha_img.attr("src");  
    captcha_img.attr('title', '点击刷新');  
    captcha_img.click(function(){
        if( verifyimg.indexOf('?')>0){  
            $(this).attr("src", verifyimg+'&random='+Math.random());
        }else{
            $(this).attr("src", verifyimg.replace(/\?.*$/,'')+'?'+Math.random());
        }
    });
  });
</script>
</body>
</html>
