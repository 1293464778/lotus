var _zKit = function(){
  this.version = '1.0.0';
  this.date = '2014-12-25 10:44';
};
_zKit.prototype.plat={
  //微信浏览器
  isWeixin: function(){
    var broswer=document.getElementById('broswer');
    var ua = window.navigator.userAgent.toLowerCase();
    return ua.match(/MicroMessenger/i) == 'micromessenger';
  },
  //安卓系统
  isAndroid:function(){
    var u = navigator.userAgent;
    return u.indexOf('Android') > -1 || u.indexOf('Linux') > -1;
  },
  //ios系统
  isIos:function(){
    var u = navigator.userAgent;
    return !!u.match(/\(i[^;]+;( U;)? CPU.+Mac OS X/);
  },
  //移动端
  isMobile:function(){
    var u = navigator.userAgent;
    return !!u.match(/AppleWebKit.*Mobile.*/);
  },
  //iPhone
  isIphone:function(){
    var u = navigator.userAgent;
    return u.indexOf('iPhone') > -1;
  },
  //iPad
  isIpad:function(){
    var u = navigator.userAgent;
    return u.indexOf('iPad') > -1;
  }
}
_zKit.prototype.win={
  //浏览器宽度
  winWidth:function(){
    if (document.documentElement && document.documentElement.clientWidth){
      return document.documentElement.clientWidth; 
    }else{
      return document.body.clientWidth;;
    }
  },
  //浏览器高度
  winHeight:function(){
    if (document.documentElement && document.documentElement.clientHeight){ 
      return document.documentElement.clientHeight;
    }else{
      return document.body.clientHeight;
    }
  },
  //滚动高度
  scrollTop:function(){
    if(document.documentElement && document.documentElement.scrollTop){
      return document.documentElement.scrollTop;
    }else{
      return document.body.scrollTop;
    }
  },
  loading:function(){
    $('body').addClass('loading');
  },
  loaded:function(){
    $('body').removeClass('loading');
  }
}
//元素宽度
_zKit.prototype.width=function(obj){
  return objWidth=obj.get(0).offsetWidth;
}
//元素高度
_zKit.prototype.height=function(obj){
  return objHeight=obj.get(0).offsetHeight;
}
_zKit.prototype.regExp={
  //去前后空格
  trim:function(str){
    return str.replace(/(^\s*)|(\s*$)/g, "");
  },
  //数字
  isNum:function(str){
    return !isNaN(str);
  },
  //email
  isEmail:function(str){
    var ptn  = /^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;
    return ptn.test(str);
  },
  //手机
  isCellphone:function(str){
    var ptn  = /^(1[0-9])\d{9}$/;
    return ptn.test(str);
  }
}
_zKit.prototype.math={
  //绝对值
  abt:function(num){
    return Math.abs(num);
  },
  //浮点数
  flt:function(num){
    return parseFloat(num);
  },
  //整数
  itg:function(num){
    return parseInt(num);
  },
  //随机数
  rdm:function(num){
    return Math.ceil(Math.random()*num);
  },
  pos1:function(num){
    var str=num.toString();
    var pos=str.indexOf('.');
    if(pos>0){
      return str.substring(0,pos+2);
    }else{
      return num;
    }
  },
  pos2:function(num){
    var str=num.toString();
    var pos=str.indexOf('.');
    if(pos>0){
      return str.substring(0,pos+3);
    }else{
      return num;
    }
  }
}
//滚轮滚动方向
_zKit.prototype.scrollDelta = function (e) {
  e = e || window.event;
  if(e.wheelDelta){  //判断浏览器IE，谷歌滑轮事件             
    if(e.wheelDelta > 0){ //当滑轮向上滚动时
      return 'up';
    }
    if(e.wheelDelta < 0){ //当滑轮向下滚动时
      return 'down';
    }
  }else if(e.detail){  //Firefox滑轮事件
    if(e.detail> 0){ //当滑轮向上滚动时
      return 'up';
    }
    if(e.detail< 0){ //当滑轮向下滚动时
      return 'down';
    }
  }
}
//id选择器
_zKit.prototype.idObj=function(name){
  return document.getElementById(name);
}
//selector选择器
_zKit.prototype.selector=function(name){
  return $('[data-selector="'+name+'"]');
}
//转json
_zKit.prototype.json=function(str){
  return eval("("+str+")");
}

_zKit.prototype.loading=function(){

}
_zKit.prototype.insertAlert=function(){
  var msgBox=this.selector('msg-box');
  if(msgBox.size()==0){
    var obgHtml='<div class="msg-box" data-selector="msg-box"><div class="msg-content"><p class="msg-txt"></p><div class="btn-wrap"><span class="msg-btn cancel-btn">取消</span><span class="msg-btn ok-btn">确定</span></div></div></div>';
    $('body').append(obgHtml);
  }
}
_zKit.prototype.alert=function(txt,callback){
  var msgBox=this.selector('msg-box');
  var okBtn=msgBox.find('.ok-btn');
  var cancelBtn=msgBox.find('.cancel-btn');
  var box=msgBox.find('.msg-txt');
  msg=(!!txt)?txt:'提示信息';
  box.html(msg);
  msgBox.show();
  cancelBtn.click(function(){
    msgBox.hide();
  });
  okBtn.click(function(){
    msgBox.hide();
    !!callback && callback();
  });
}

_zKit.prototype.validate=function(form,callback){
  var self=form,
      btn=self.find('[type=submit]'),
      file=self.find('[data-validate="1"]'),
      go=true,
      errorTip=self.find('.error-tip');
      errorTip.html('');
  var setError=function(msg,obj){
    layer.open({
      content: msg,
      style: 'background-color:#f9f2f4; color:#c7254e; border:none;',
      time: 2
    });
    obj.focus().addClass('has-error');;
    go=false;
  }
  for(var i=0;i<file.size();i++){
    var obj=file.eq(i),wrap=obj.parents('.form-group');
    var json=z.json(obj.data('rules'));
    obj.removeClass('has-error');
    if(json.required==1){
      var val=z.regExp.trim(obj.val()),msg=json["msg"],type=json["type"];
      var node=obj[0].tagName;
      if(val==''){
        var msg=(node=='SELECT')&&('请选择'+msg)||('请填写'+msg);
        setError(msg,obj);
        break;
      }
      if(type=='cellphone'){
        if(!z.regExp.isCellphone(val)){
          var msg='请正确填写'+msg;
          setError(msg,obj);
          break;
        }
      }
      if(type=='number'){
        if(isNaN(val)){
          var msg='请正确填写'+msg;
          setError(msg,obj);
          break;
        }
      }
      if(json["minlength"]){
        var len=json["minlength"];
        if(val.length<len){
          var msg=msg+'长度不能小于'+len+'位！';
          setError(msg,obj);
          break;
        }
      }
      if(json["maxlength"]){
        var len=json["maxlength"];
        if(val.length>len){
          var msg=msg+'长度不能大于'+len+'位！';
          setError(msg,obj);
          break;
        }
      }
      if(json["compare"]){
        var tgt=json["compare"];
        var tVal=$('[name='+tgt+']').val();
        if(val!=tVal){
          var msg=msg+'不一致';
          setError(msg,obj);
          break;
        }
      }
    }
  }
  if(go==true){
    callback();
  }
  return go;
}
_zKit.prototype.submit=function(form,btn){
  var btntxt=btn.html();
  btn.html('处理中...').attr('disabled',true);
  $.ajax({
    url:form.attr('action'),
    type:form.attr('method'),
    data:form.serialize(),
    dataType:'json',
    success:function(d){
      btn.html(btntxt).attr('disabled',false);
      if(d.flag==1){
        setTimeout(function(){
          layer.open({
            content: d["msg"],
            style: 'background-color:#FFF; border:none;',
            time: 2
          });
        },500);
        if(form.data('jump')){
          setTimeout(function(){
            window.location.href=form.data('jump');
          },2000);
        }else if(d["redirectUrl"]){
          setTimeout(function(){
            window.location.href=d["redirectUrl"];
          },2000);
        }else{
          setTimeout(function(){
            window.location.href=window.location;
          },2000);
        }
      }else{
        setTimeout(function(){
          layer.open({
            content: d["msg"],
            style: 'background-color:#FFF; border:none;',
            time: 2
          });
        },500);
      }
    }
  });
}

var z = window.z = new _zKit();


//EXP
// console.log(z.win.winWidth());
// console.log(z.win.winHeight());
// console.log(z.plat.isWeixin());
// console.log(z.math.abt(-123));
// console.log(z.math.itg(-123.33));
// console.log(z.math.flt(-123.3456));
// console.log(z.regExp.trim(' hello '));
// console.log(z.regExp.isNum(123));
// console.log(z.regExp.isEmail('123'));
// console.log(z.regExp.isCellphone('13191668863'));


