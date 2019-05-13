<?php if (!defined('THINK_PATH')) exit(); /*a:1:{s:39:"./themes/default/index/layer_login.html";i:1556087324;}*/ ?>
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
<link href="<?php echo config('theme_path'); ?>/index/css/login.css" rel="stylesheet">
<!--[if lt IE 9]>
    <script src="<?php echo config('theme_path'); ?>/index/js/html5shiv.min.js"></script>
    <script src="<?php echo config('theme_path'); ?>/index/js/respond.min.js"></script>
<![endif]-->
</head>
<body>
      
        <div style="width:300px;text-align:center;margin:0 auto;">
        <!--login start-->
        <form action="" id="form" method="POST">
          <div class="main_member">会员登录</div>
          <div class="input-group " style="margin-top:20px">  
            <input type="text" id="username" name="key" class="form-control" placeholder="请输入手机号/用户名" >
            <span class="input-group-addon back" ><img  src="<?php echo config('theme_path'); ?>/index/images/icon_Member_login.png"></span>
          </div>
          <div class="input-group" style="margin-top:20px">
            <input type="password" id="password" name="password" class="form-control" placeholder="请输入密码" >
            <span class="input-group-addon back" ><img src="<?php echo config('theme_path'); ?>/index/images/icon_password.png"></span>          
          </div>
        <div class="input-group" style="margin-top:20px;">
          <button type="button" id="submit" class="  btn_btn" style="width:300px;">会员登录</button>        
        </div>
        </form>
        <!--login end-->
        <div class="input-group pass">
           <span id="forget" style="cursor:pointer">忘记登录密码？</span><span style="margin-left:170px;cursor:pointer" id="reg">注册</span>
        <div style="margin-top:20px;">
        <p style="text-align:center"><hr class="hr-left"/><span style="margin-left:18px;margin-top:5px">使用第三方账号登录</span><hr class="hr-right" /></p>
            <div class="login_index">
            <a href=""><img src="<?php echo config('theme_path'); ?>/index/images/login_wb.png" /></a>
            <a href=""><img src="<?php echo config('theme_path'); ?>/index/images/login_wx.png" /></a>
            <a href=""><img src="<?php echo config('theme_path'); ?>/index/images/login_qq.png" /></a></div>
        </div>
       </div>
      </div>

<!--main end-->

<!-- jQuery (necessary for Bootstrap's JavaScript plugins) --> 
<script src="<?php echo config('theme_path'); ?>/index/js/jquery.min.js"></script> 
<!-- Include all compiled plugins (below), or include individual files as needed --> 
<script src="<?php echo config('theme_path'); ?>/index/js/bootstrap.min.js"></script>

<script type="text/javascript">
  $('#submit').click(function(){
  var key = $("#username").val();
  var password = $("#password").val();
  var data = $('#form').serialize();

  if(key ==""||key =="请输入手机号/用户名")
  {
     alert("请输入手机号或用户名");
     return false;  
  }
  if(password ==""||password =="请输入密码")
  {
     alert("请输入密码");
     return false;  
  }
  parent.window.closeLayer(data);
});
  //注册
  $('#reg').click(function(){
    parent.window.closeToAction('reg');
  })
  //注册
  $('#forget').click(function(){
    parent.window.closeToAction('forget');
  })
</script>
</body>
</html>