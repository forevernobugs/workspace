/*** 
*评论*
****/
var limitTime=null;

function quotecomment(oo){
	document.getElementById("comment_content").value=oo;
	document.getElementById("comment_content").focus();
}

function limitComment(){
	limitTime=limitTime-1;
	if(limitTime>0){
		document.getElementById("comment_content").value='还剩'+limitTime+'秒,你才可以再发表评论';
		document.getElementById("comment_content").disabled=true;
		document.getElementById("comment_submit").disabled=true;
		setTimeout("limitComment()",1000);
	}else if(limitTime==0){
		document.getElementById("comment_content").value='';
		document.getElementById("comment_content").disabled=false;
		document.getElementById("comment_submit").disabled=false;
	}
	
}
//旧版的需要用到
function postcomment(url,yzimgnum){
	var yzimgstr='';
	//if(yzimgnum=='1'){
	if(document.getElementById("yzImgNum")!=null){
		yzimgstr+="&yzimg="+ document.getElementById("yzImgNum").value;
	}
	if(document.getElementById("commentface")!=null){
		yzimgstr+="&commentface="+ document.getElementById("commentface").value;
	}
	username4 = document.getElementById("comment_username").value;
	content4 = document.getElementById("comment_content").value;
	if(content4==''){
		showerr("内容不能为空");
		return false;
	}
	content4=content4.replace(/(\n)/g,"@@br@@");
	//document.getElementById("comment_content").value='';
	//document.getElementById("comment_content").disabled=true;
	limitTime=10;
	limitComment();
	
	AJAX.get("comment",url + "&username=" + username4 + "&content=" + content4 + yzimgstr ,0);
	//if(yzimgnum=='1'){
	if(document.getElementById("yzImgNum")!=null){
		//document.getElementById("yz_Img").src;
		document.getElementById("yzImgNum").value='';
	}
}
function showerr(msg){
	alert(msg);
}
function getcomment(url){
	AJAX.get("comment",url,1);
}

function ShowMenu_mmc(){
}
function HideMenu_mmc(){
}

function get_position(o){//取得坐标
	var to=new Object();
	to.left=to.right=to.top=to.bottom=0;
	var twidth=o.offsetWidth;
	var theight=o.offsetHeight;
	while(o!=document.body){
		to.left+=o.offsetLeft;
		to.top+=o.offsetTop;
		o=o.offsetParent;
	}
	to.right=to.left+twidth;
	to.bottom=to.top+theight;
	return to;
}

/***
*在线操作*
****/
document.write('<div style="display:none;"><table width="100%" border="0" cellspacing="0" cellpadding="0" id="AjaxEditTable"><tr><td class="head"><h3 class="L"></h3><h3 class="R"></h3><span class="eidtmodule" onclick="this.offsetParent.offsetParent.offsetParent.style.display=\'none\'" onMouseOver="this.style.cursor=\'hand\'">关闭</span></td></tr><tr> <td class="middle"></td></tr></table></div>');
var clickEdit={
	tableid:null,
	init:function(){
		oo=document.body.getElementsByTagName("A");
		for(var i=0;i<oo.length;i++){
			if(oo[i].getAttribute("editurl")!=null){
				if(oo[i].getAttribute("href")!=null)oo[i].href='javascript:';
				oo[i].title='点击可以修改这里的设置';
				if (document.all) { //For IE
					oo[i].attachEvent("onmousedown",clickEdit.showdiv);
					oo[i].attachEvent("onmouseover",clickEdit.showstyle);
					oo[i].attachEvent("onmouseout",clickEdit.hidestyle);
				}else{ //For Mozilla
					oo[i].addEventListener("onmousedown".substr(2,"onmousedown".length-2),clickEdit.showdiv,true);
				}
			}
		}
	},
	showstyle:function(evt){
		var evt = (evt) ? evt : ((window.event) ? window.event : "");
		if (evt) {
			 ao = (evt.target) ? evt.target : evt.srcElement;
		}
		ao.style.border='1px dotted red';
		ao.style.cursor='hand';
	},
	hidestyle:function(evt){
		var evt = (evt) ? evt : ((window.event) ? window.event : "");
		if (evt) {
			 ao = (evt.target) ? evt.target : evt.srcElement;
		}
		ao.style.border='0px dotted red';
	},
	showdiv:function(evt){
		var w=150;
		var h=100;
		var evt = (evt) ? evt : ((window.event) ? window.event : "");
		if (evt) {
			 ao = (evt.target) ? evt.target : evt.srcElement;
		}
		//oid=ao.offsetParent.offsetParent.id;
		//获取坐标的函数头部有定义
		position=get_position(ao);
		
		//alert(oid);
		url=ao.getAttribute("editurl");
		oid=url.replace(/(\.|=|\?|&|\\|\/|:)/g,"_");
		ao.id=oid;
		DivId="clickEdit_"+oid;
		url=url + "&TagId=" + oid;
		obj=document.getElementById(DivId);
		if(obj==null){
			obj=document.createElement("div");

			obj.innerHTML=document.getElementById('AjaxEditTable').outerHTML;
			objs=obj.getElementsByTagName("TD");
			objs[1].id=DivId;
			//obj.id=DivId;
			//obj.className="Editdiv";
			obj.style.Zindex='999';
			//obj.style.display='';
			obj.style.position='absolute';
			obj.style.top=position.bottom;
			obj.style.left=position.left;
			obj.style.height=h;
			obj.style.width=w;
			document.body.appendChild(obj);
			//obj.innerHTML='以下是显示内容...';
			AJAX.get(DivId,url,1);
		}else{
			fobj=obj.offsetParent.offsetParent;
			if(fobj.style.display=='none'){
				fobj.style.display='';
			}else{
				fobj.style.display='none';
			}
		}
	},
	save:function(oid,job,va){
		divid="clickEdit_"+oid;
		//alert(oid)
		//GET方式提交内容,如果有空格的话.会有BUG
		//即时显示,不过没判断是否保存成功也显示了
		document.getElementById(oid).innerHTML=va;
		va=va.replace(/(\n)/g,"@BR@");
		AJAX.get(divid,"ajax.php?inc="+job+"&step=2&TagId="+oid+"&va="+va,0);
	},
	cancel:function(divid){
		document.getElementById(divid).offsetParent.offsetParent.style.display='none';
	}
}

//显示子栏目
function showSonName(fid)
{
	oo=document.body.getElementsByTagName('DIV');
	for(var i=0;i<oo.length;i++){
		if(oo[i].className=='SonName'+fid){
			if(oo[i].style.display=='none'){
				oo[i].style.display='';
			}
			else
			{
				oo[i].style.display='none';
			}
		}
	}
}

function avoidgather(myname){
	fs=document.body.getElementsByTagName('P');
	for(var i=0;i<fs.length;i++){
		if(myname!=''&&fs[i].className.indexOf(myname)!=-1){
			fs[i].style.display='none';
		}
		
	}
	fs=document.body.getElementsByTagName('DIV');
	for(var i=0;i<fs.length;i++){
		if(myname!=''&&fs[i].className.indexOf(myname)!=-1){
			fs[i].style.display='none';
		}
	}
}

function dblclick_label(){
	if(/jobs=show$/.test(location.href)){
		a=confirm('你是否要退出标签管理');
		if (a){
			window.location.href=location.href+'abc';
		}
	}else{
		b=confirm('你是否要进入标签管理');
		if (b){
			url = location.href
			if (/\?/.test(url)){
				window.location.href=url+'&jobs=show';
			}else{
				window.location.href=url+'?jobs=show';
			}
		}
	}
}






/*
* myFocus JavaScript Library v1.2.4
* Open source under the BSD & GPL License.
* 
* @Author  koen_lee@qq.com
* @Blog    http://hi.baidu.com/koen_li/
* 
* @Date    2011/07/20
*/
eval(function(p,a,c,k,e,r){e=function(c){return(c<a?'':e(parseInt(c/a)))+((c=c%a)>35?String.fromCharCode(c+29):c.toString(36))};if(!''.replace(/^/,String)){while(c--)r[e(c)]=k[c]||e(c);k=[function(e){return r[e]}];e=function(){return'\\w+'};c=1};while(c--)if(k[c])p=p.replace(new RegExp('\\b'+e(c)+'\\b','g'),k[c]);return p}('(6(){8 q={1q:{C:\'4M\',4L:\'3e\',22:\'4r\',1E:L,K:L,3c:4,u:0,1A:20,4k:X,O:L,15:\'38://35.34.33/31/30/C/\',1M:J},C:{},1t:6(){8 a=2V,l=a.M,i=1,1y=a[0];7(l===1){i=0,1y=9.C}G(i;i<l;i++){G(8 p 1x a[i])7(!(p 1x 1y))1y[p]=a[i][p]}}};8 r={$:6(a){x 1f a===\'2S\'?V.3P(a):a},$$:6(a,b){x(9.$(b)||V).3O(a)},$$1R:6(b,c){8 d=[],a=9.$$(b,c);G(8 i=0;i<a.M;i++){7(a[i].2Q===c)d.Y(a[i]);i+=9.$$(b,a[i]).M}x d},$c:6(a,b){8 c=9.$$(\'*\',b),a=a.1p(/\\-/g,\'\\\\-\'),1G=1r 3J(\'(^|\\\\s)\'+a+\'(\\\\s|$)\'),1Q=[];G(8 i=0,l=c.M;i<l;i++){7(1G.3I(c[i].U)){1Q.Y(c[i]);2P}}x 1Q[0]},$I:6(a,b){x 9.$$1R(\'I\',9.$c(a,b))},1E:6(a,b){8 c=V.1H(\'3F\');c.U=b;a[0].2Q.2O(c,a[0]);G(8 i=0;i<a.M;i++)c.27(a[i])},3D:6(a,b){a.1h=\'<12 2N=\'+b+\'>\'+a.1h+\'</12>\'},3B:6(a,b){8 s=[],12=9.$$(\'12\',a)[0],I=9.$$1R(\'I\',12),D,n=I.M,1S=b.M;G(8 j=0;j<1S;j++){s.Y(\'<12 2N=\'+b[j]+\'>\');G(8 i=0;i<n;i++){D=9.$$(\'D\',I[i])[0];s.Y(\'<I>\'+(b[j]==\'1S\'?(\'<a>\'+(i+1)+\'</a>\'):(b[j]==\'1e\'&&D?I[i].1h.1p(/\\<D(.|\\n|\\r)*?\\>/i,D.3A)+\'<p>\'+D.2g("19")+\'</p>\':(b[j]==\'2L\'&&D?\'<D H=\'+(D.2g("2L")||D.H)+\' />\':\'\')))+\'<23></23></I>\')};s.Y(\'</12>\')};a.1h+=s.24(\'\')}},2I={y:6(o,a){8 v=(9.1i?o.4w:4q(o,\'\'))[a],2c=4h(v);x 2F(2c)?v:2c},2E:6(o,a){o.y.46="43(P="+a+")",o.y.P=a/X},2A:6(o,a){8 b=o.U,1G="/\\\\s*"+a+"\\\\b/g";o.U=b?b.1p(2z(1G),\'\'):\'\'}},2y={1D:6(a,f,g,h,i,j){8 k=f===\'P\',F=9,P=F.2E,2x=1f g===\'2S\',2r=(1r 2q).2p();7(k&&F.y(a,\'1v\')===\'1Z\')a.y.1v=\'3S\',P(a,0);8 l=F.y(a,f),b=2F(l)?1:l,c=2x?g/1:g-b,d=h||3R,e=F.2n[i||\'2m\'],m=c>0?\'3q\':\'2k\';7(a[f+\'1m\'])1n(a[f+\'1m\']);a[f+\'1m\']=29(6(){8 t=(1r 2q).2p()-2r;7(t<d){k?P(a,1o[m](e(t,b*X,c*X,d))):a.y[f]=1o[m](e(t,b,c,d))+\'A\'}T{1n(a[f+\'1m\']),k?P(a,(c+b)*X):a.y[f]=c+b+\'A\',k&&g===0&&(a.y.1v=\'1Z\'),j&&j.2i(a)}},13);x F},3k:6(a,b,c){9.1D(a,\'P\',1,b==N?2M:b,\'2e\',c);x 9},40:6(a,b,c){9.1D(a,\'P\',0,b==N?2M:b,\'2e\',c);x 9},2j:6(a,b,c,d,e){G(8 p 1x b)9.1D(a,p,b[p],c,d,e);x 9},3E:6(a){G(8 p 1x a)7(p.3L(\'1m\')!==-1)1n(a[p]);x 9},2n:{2e:6(t,b,c,d){x c*t/d+b},3T:6(t,b,c,d){x-c/2*(1o.44(1o.4a*t/d)-1)+b},4j:6(t,b,c,d){x c*(t/=d)*t*t*t+b},2m:6(t,b,c,d){x-c*((t=t/d-1)*t*t*t-1)+b},4m:6(t,b,c,d){x((t/=d/2)<1)?(c/2*t*t*t*t+b):(-c/2*((t-=2)*t*t*t-2)+b)}}},2l={1N:6(p,b,c){7(1f b!==\'3w\')c=b,b=J;8 F=9,1z=0;p.C=p.C||F.1q.C,p.15=p.15==N?F.1q.15:p.15,p.S=p.C+\'-\'+p.Q;6 21(){7(1z==2){7(p.1M)F.2o(p.Q,p.E,p.B);F.C[p.C].2i(F,p,F);c&&c()}};6 1s(){8 a=F.$(p.Q);a.y.B=4g+\'A\';F.2s(p.C,p.15,6(){F.1t(p,F.C[p.C].2t,F.1q);p.E=p.E||F.y(a,\'E\'),p.B=p.B||F.y(a,\'B\');F.2u(p),a.U+=\' \'+p.C+\' \'+p.S,a.y.B=\'\';1z+=1,21()});F.2v(a,p.1A==N?F.1q.1A:p.1A,6(){1z+=1,21()})};7(b){1s();x}7(3u.2w){(6(){3z{1s()}3C(e){1V(2V.3G,0)}})()}T{F.1C(V,\'3M\',1s)}},2u:6(p){8 a=[],w=p.E,h=p.B,1b=V.1H(\'y\');1b.2B=\'19/O\';7(p.1E)9.1E([9.$(p.Q)],p.C+\'3V\');7(p.O)a.Y(\'.\'+p.S+\' *{3W:0;2C:0;41:0;42-y:1Z;}.\'+p.S+\'{1L:2D;E:\'+w+\'A;B:\'+h+\'A;1K:1J;4c:4d/1.5 4e;19-2f:2d;2G:#2H;4o:4p!2b;}.\'+p.S+\' .2a{1L:4B;z-u:3h;E:X%;B:X%;3i:#3j;19-2f:25;2C-3l:\'+0.3*h+\'A;2G:#2H 3m(38://35.34.33/31/D/2a.3n) 25 \'+0.4*h+\'A 3o-3p;}.\'+p.S+\' .2J{1L:2D;E:\'+w+\'A;B:\'+h+\'A;1K:1J;}.\'+p.S+\' .1e I,.\'+p.S+\' .1e I 23,.\'+p.S+\' .1e-3r{E:\'+w+\'A;B:\'+p.22+\'A!2b;3s-B:\'+p.22+\'A!2b;1K:1J;}.\'+p.S+\' .1e I p a{1v:3t;}\');7(p.O&&p.1M)a.Y(\'.\'+p.S+\' .2J I{19-2f:25;E:\'+w+\'A;B:\'+h+\'A;}\');7(1b.2K){1b.2K.3v=a.24(\'\')}T{1b.1h=a.24(\'\')}8 b=9.$$(\'1Y\',V)[0];b.2O(1b,b.3x)}},2h={1i:!(+[1,]),3y:6(a,b,c,d,e){x"8 11=9,1W=11.$c(\'2a\',1g),W="+c+",10,26=L,17="+d+"||\'2d\',1B=17==\'2d\'||17==\'3H\'?1j.E:1j.B,1w=W&&("+e+"||3K),u=1j.u,1u=1j.3c*2R;7(W){1w.y[17]=-1B*n+\'A\';u+=n;}7(1W)1g.3N(1W);8 R=6(14){("+a+")();8 3Q=u;7(W&&u==2*n-1&&10!=1){1w.y[17]=-(n-1)*1B+\'A\';u=n-1}7(W&&u==0&&10!=2){1w.y[17]=-n*1B+\'A\';u=n}7(!W&&u==n-1&&14==N)u=-1;7(W&&14!==N&&u>n-1&&!10&&!26) 14+=n;8 1c=14!==N?14:u+1;7("+b+")("+b+")();u=1c;10=26=2T;};R(u);7(1u&&1j.K)8 K=29(6(){R()},1u);11.1C(1g,\'2U\',6(){7(K)1n(K)});11.1C(1g,\'3U\',6(){7(K)K=29(6(){R()},1u)});G(8 i=0,1T=11.$$(\'a\',1g),2W=1T.M;i<2W;i++) 1T[i].3X=6(){9.3Y();}"},3Z:6(a,b,c){x"G (8 j=0;j<n;j++){"+a+"[j].u=j;7("+b+"==\'3e\'){"+a+"[j].2X=6(){7(9.u!=u)9.U+=\' 2Y\'};"+a+"[j].2Z=6(){11.2A(9,\'2Y\')};"+a+"[j].1I=6(){7(9.u!=u) {R(9.u);x J}};}T 7("+b+"==\'2U\'){"+a+"[j].2X=6(){8 1d=9;7("+c+"==0){7(1d.u!=u){R(1d.u);x J}}T "+a+".d=1V(6(){7(1d.u!=u) {R(1d.u);x J}},"+c+")};"+a+"[j].2Z=6(){45("+a+".d)};}T{32(\'47 48 : \\"\'+"+b+"+\'\\"\');2P;}}"},49:6(a,b,c){x"8 1F=J;"+a+".1I=6(){9.U=9.U==\'"+b+"\'?\'"+c+"\':\'"+b+"\';7(!1F){1n(K);K=2T;1F=L;}T{K=L;1F=J;}}"},4b:6(a,b,c,d,e){x"8 16={},Z="+c+",36=1o.2k("+d+"/2),37=4f("+a+".y["+b+"])||0,1a=1c>=n?1c-n:1c,39="+e+"||4i,3a=Z*(n-"+d+"),1X=Z*1a+37;7(1X>Z*36&&1a!==n-1) 16["+b+"]=\'-\'+Z;7(1X<Z&&1a!==0) 16["+b+"]=\'+\'+Z;7(1a===n-1) 16["+b+"]=-3a;7(1a===0) 16["+b+"]=0;11.2j("+a+",16,39);"},4l:6(a,b){x a+".1I=6(){10=1;R(u>0?u-1:n-1);};"+b+".1I=6(){10=2;8 3b=u>=2*n-1?n-1:u;R(u==n-1&&!W?0:3b+1);}"},4n:6(o,a,b){8 c=9.$$(\'D\',o)[0];c.H=b?c.H.1p(2z("/"+a+"\\\\.(?=[^\\\\.]+$)/g"),\'.\'):c.H.1p(/\\.(?=[^\\.]+$)/g,a+\'.\')},2v:6(a,b,c){8 d=9.$$(\'D\',a),1k=d.M,28=0,1l=J;G(8 i=0;i<1k;i++){d[i].3d=6(){28+=1;7(28==1k&&!1l){1l=L,c()}};7(9.1i)d[i].H=d[i].H};7(b===L)x;8 t=b===J?0:b*2R;1V(6(){7(!1l){1l=L,c()}},t)},2o:6(a,b,c){8 d=9.$$(\'D\',a),1k=d.M,18=1r 4s();G(8 i=0;i<1k;i++){18.H=d[i].H;7(18.E/18.B>=b/c){d[i].y.E=b+\'A\';d[i].y.4t=(c-b/18.E*18.B)/2+\'A\'}T{d[i].y.B=c+\'A\'}}},2s:6(a,b,c){7(!b){c();x}8 d=V.1H("4u"),O=V.1H("4v"),H=b+a+\'.30\',1U=b+a+\'.O\';d.2B="19/4x",d.H=H;O.4y="4z",O.1U=1U;9.$$(\'1Y\')[0].27(O);9.$$(\'1Y\')[0].27(d);7(9.1i){d.4A=6(){7(d.3f=="4C"||d.3f=="4D")c()}}T{d.3d=6(){c()}}d.4E=6(){32(\'4F 4G (4H): \'+H)}},1C:6(a,c,d){8 b=9.1i,e=b?\'2w\':\'4I\',t=(b?\'4J\':\'\')+c;a[e](t,d,J)}};q.1t(q,r,2I,2y,2l,2h);q.1N.4K=6(a,p){q.C[a].2t=p};1P=q;7(1f 1O===\'N\')1O=1P;7(1f 3g!==\'N\'){3g.4N.1t({1O:6(p,a){7(!p)p={};p.Q=9[0].Q;7(!p.Q)p.Q=9[0].Q=\'4O\';1P.1N(p,L,a)}})}})();',62,299,'||||||function|if|var|this|||||||||||||||||||||index|||return|style||px|height|pattern|img|width||for|src|li|false|auto|true|length|undefined|css|opacity|id|run||else|className|document|less|100|push|scDis|_tn|_F|ul||idx|path|scPar|_dir|IMG|text|scIdx|oStyle|next|self|txt|typeof|box|innerHTML|isIE|par|len|ok|Timer|clearInterval|Math|replace|defConfig|new|ready|extend|_t|display|_wp|in|parent|cont|waiting|_dis|addEvent|animate|wrap|_stop|reg|createElement|onclick|hidden|overflow|position|autoZoom|set|myFocus|myFocus__AGENT__|arr|_|num|_lk|href|setTimeout|_ld|scD|head|none||show|txtHeight|span|join|center|first|appendChild|count|setInterval|loading|important|pv|left|linear|align|getAttribute|Method|call|slide|floor|Init|easeOut|easing|fixIMG|getTime|Date|st|loadPattern|cfg|initCSS|onloadIMG|attachEvent|am|Anim|eval|removeClass|type|padding|relative|setOpa|isNaN|background|fff|CSS|pic|styleSheet|thumb|400|class|insertBefore|break|parentNode|1000|string|null|mouseover|arguments|_ln|onmouseover|hover|onmouseout|js|myfocus|alert|co|haoniu|www|scN|scDir|http|scDur|scMax|tIdx|time|onload|click|readyState|jQuery|9999|color|666|fadeIn|top|url|gif|no|repeat|ceil|bg|line|inline|window|cssText|boolean|firstChild|switchMF|try|alt|addList|catch|wrapIn|stop|div|callee|right|test|RegExp|pics|indexOf|DOMContentLoaded|removeChild|getElementsByTagName|getElementById|prev|800|block|swing|mouseout|_wrap|margin|onfocus|blur|bind|fadeOut|border|list|alpha|cos|clearTimeout|filter|Error|Setting|toggle|PI|scroll|font|12px|Verdana|parseInt|314|parseFloat|500|easeIn|delay|turn|easeInOut|alterSRC|visibility|visible|getComputedStyle|default|Image|marginTop|script|link|currentStyle|javascript|rel|stylesheet|onreadystatechange|absolute|loaded|complete|onerror|Not|Found|404|addEventListener|on|params|trigger|mF_fscreen_tb|fn|mF__NAME__'.split('|'),0,{}))