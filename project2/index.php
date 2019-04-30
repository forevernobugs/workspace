<?php
    header("content-type:text/html;charset=utf-8");
    include('product_array.php');
    //对元素进行排序
    foreach($pro1 as $k=>$v){
        sort($v);
        $pro1[$k]=$v;
    }
    //连接数据库
    $dsn='mysql:host=127.0.0.1;port=3306;dbname=data;charset=utf8';
    $pdo=new PDO($dsn,'root','root');
    $inputres=false;//保存数据结果
    $insertOk=false;//保存数据结果
    $data = [];//要展示的数据
     if(isset($_POST['key'])){
      echo json_encode($pro1[$_POST['key']]);die;
    }
    //处理请求
    $flag=isset($_POST['flag'])?$_POST['flag']:'';//判定提交的表格
    switch ($flag) {
      case 'input':
        $name=strtolower($_POST['name']);
        $product=$_POST['pro'];
        $classname=$_POST['classname'];
        $res=$pdo->query("select * from s_data where name='{$name}'")->fetch(PDO::FETCH_NUM);
        if($res){
            $inputres=true;
        }else{
           $pdo->exec("insert into s_data values (null,'$name','$product','$classname')");
           $insertOk=true;
        }
        //获取列表信息
        $data=$pdo->query("select * from s_data order by id desc")->fetchAll();
        break;
      case 'getdata':
        $name=isset($_POST['name'])?$_POST['name']:'';
        $product=isset($_POST['pro'])?$_POST['pro']:'';
        $classname=isset($_POST['classname'])?$_POST['classname']:'';
        
        $sql="select * from s_data where 1 ";
        if($name){
            $sql.= "and name='$name' ";
        }
        if($product!='all'){
            $sql.= "and product='$product' ";
        }
        if($product!='all' && $classname){
            $sql.= " and classname='$classname' ";
        }
        $sql.=" order by id desc";
        $data=$pdo->query($sql)->fetchAll();
        break;
      default:
        //获取列表信息
        $data=$pdo->query("select * from s_data order by id desc")->fetchAll();
        break;
    }

?>
<!DOCTYPE HTML>
<html>
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
</head>
<body>
<div class="home">
  <div class='body'>
    <form action="" class='load1' method="post">
      <input type="hidden" value='input' name='flag'>
       输入：
          <table class='shuru'>
                <tr>
                    <td width="100" class="text-r" style="text-align: right;">英文名：</td>
                    <td><input type="text" style="width:200px" placeholder="" name="name" id="A1" datatype="*0-16" nullmsg="用户名不能为空"></td>
                </tr>
                <tr>
                    <td width="100" class="text-r"></td>
                    <td id="ss">
                      <input type="button" name="pro" id="A2" value="键A2">
                        <div id="box">

                        </div>
                    </td>
                </tr>
          </table>
          <input type="submit" value="键A6" id="A6" style="margin-left: 600px"><span id="msg"></span>
    </form>
    <br>
    <form action="" class='load2' method="POST" id='search'>
      <input type="hidden" value='getdata' name='flag'>
       搜索：
        名字：<input type="text" style="width:200px" placeholder="" name="name" id="B1" datatype="*0-16" nullmsg="姓名不能为空">
        <select name='pro' id="B2"  onfocus="selectFocus(this)">
          <option value ="all" >All</option>
          <?php
              foreach ($pro as $key => $value) {
                  echo "<option value ='".$key."' onclick='selectClick(this)'>".$value."</option>"; 
              }
          ?>
        </select>
        <select name='classname' id="B3" style="display: none" onfocus="selectFocus(this)">
          <option value ="all" >All</option>
        </select>
        <input type="submit" value="键B4" >
    </form> 
    <br>
    <table class="table"  style="text-align:center;vertical-align:middle;">
      显示：
            <tr class="text-c">
              <th width="100">ID</th>
              <th width="100">name</th>
              <th width="100">产品类别</th>
              <th width="100">产品分类</th>
            </tr>
            <?php 
              foreach ($data as $key => $value) {
            ?>
                <tr class="text-c">
                  <td width="100"><?php echo $value['id'] ?></td>
                  <td width="100"><?php echo $value['name'] ?></td>
                  <td width="100"><?php echo $pro[$value['product']] ?></td>
                  <td width="100"><?php echo $value['classname'] ?></td>
                </tr>
            <?php
              } 
            ?>  
          
  </table>
  </div>
</div>
<div id="box1">
  <div id="child">
    <select name='pro' id="A3" style="display: none" onfocus="selectFocus(this)">
      <option value ="all" >All</option>
      <?php
          foreach ($pro as $key => $value) {
              echo "<option value ='".$key."' onclick='selectClick(this)'>".$value."</option>"; 
          }
      ?>
    </select>
    <select name='classname' id="A4" style="display: none" onfocus="selectFocus(this)">
      <option value ="all" >All</option>
    </select>
    <input type="reset" name="" id="A5" value="键A5">
  </div>
</div>

<input type="hidden" id='inputres' value="<?php echo $inputres ?>">
<input type="hidden" id='insertOk' value="<?php echo $insertOk ?>">
<script>
    var inputres=document.getElementById('inputres').value;
    var insertOk=document.getElementById('insertOk').value;
    if(inputres){
        alert("DID EXIST");
    }
    if(insertOk){
        alert("INPUT SUCCESSFULLY");
    }
</script>
<script type="text/javascript">
    child = 'child'
    var msg=document.getElementById('msg').value
    var A2=document.getElementById('A2')
    var A3=document.getElementById(child).children[0]
    var A4=document.getElementById(child).children[1]
    var A5=document.getElementById(child).children[2]
    var childiv = document.getElementById(child)
    // var A4=document.createElement('select');
    A3.onchange = function(){
        let key = A3.selectedIndex;
        if(key !=0){
          //发送post请求
          //第一步 创建ajax对象
          var xhr = new XMLHttpRequest();
          //第二步 设置回调函数
          xhr.onreadystatechange = function(){
          //第五步 判断与处理数据
          if(xhr.readyState == 4){
            // 4  代表数据接收完成
            var res = xhr.responseText
            let arr = JSON.parse(res)
            var html = '';
            for(var i in arr){
              html += "<option class='input' value = '" + arr[i] +"' onclick='selectClick(this)'>" + arr[i] + "</option>"
            }
            A4.innerHTML = html;
      }
    }
          //第三步 设置请求方式和请求地址
          xhr.open('post', '');
            //设置post请求 请求头信息
          xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
          //第四步 发送请求
          xhr.send('key=' + key);
        }
    
    };
    A2.onclick = function(){
      A3.style.display="inline";
      child = child + '1';
      childiv.id = child
      document.getElementById('box').innerHTML += document.getElementById('box1').innerHTML
    } 
    A5.onclick = function(){
      A3.style.display="none";
      A4.style.display="none";
    }
    A3.onclick = function(){

      A4.style.display='inline'

    }
    //点击添加size属性
    function selectFocus(that){
      that.setAttribute("size","5");
    } 
    //点击删除size属性
    function selectClick(that){
      that.parentNode.removeAttribute("size");
      that.blur();
      that.setAttribute("selected",""); 
    } 
    var B2=document.getElementById('B2');
    var B3=document.getElementById('B3'); 
    B2.onclick = function(){
      B3.style.display="inline";
    }
    B2.onchange=function(){
      let key = B2.selectedIndex;
        if(key !=0){
          //发送post请求
          //第一步 创建ajax对象
          var xhr = new XMLHttpRequest();
          //第二步 设置回调函数
          xhr.onreadystatechange = function(){
          //第五步 判断与处理数据
          if(xhr.readyState == 4){
            // 4  代表数据接收完成
            var res = xhr.responseText
            let arr = JSON.parse(res)
            var html = '';
            for(var i in arr){
              html += "<option class='search' value = '" + arr[i] +"' onclick='selectClick(this)'>" + arr[i] + "</option>"
            }
            B3.innerHTML = html;
          }
        }
        //第三步 设置请求方式和请求地址
          xhr.open('post', '');
            //设置post请求 请求头信息
          xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
          //第四步 发送请求
          xhr.send('key=' + key);
        }
    };
</script>
</body>
</html>