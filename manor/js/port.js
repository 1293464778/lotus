  var baselingk="http://longhuzy.com/index.php/Api/";
  var urldate = {
    "login":baselingk+"Login/login",
    "getCode":baselingk+"Login/getCode",
    "forget":baselingk+"Login/forget",
    "notice":baselingk+"Sys/notice",
    "static":baselingk+"Static/index",
    "remind":baselingk+"Index/remind",
    "grown":baselingk+"Log/grown",
    "seed":baselingk+"Log/seed",

    "fertilizer":baselingk+"Log/fertilizer",
    "sown":baselingk+"Log/sown",
    "gets":baselingk+"Log/get",
    "props":baselingk+"Log/prop",
    "picklist":baselingk+"Garden/picklist",
    "getInfo":baselingk+"Center/getInfo",
    "recommen":baselingk+"Center/recommen",
    "isBuyCode":baselingk+"Order/isBuyCode",
    "getNum":baselingk+"Order/getNum",
    "commission":baselingk+"Order/commission",
    "direction":baselingk+"Order/direction",
    "getCode1":baselingk+"Order/getCode",
    "commissionList":baselingk+"Order/commissionList",
    "directionList":baselingk+"Order/directionList",
    "checkSecPwd":baselingk+"Center/checkSecPwd",
    "commitDirection":baselingk+"Order/commitDirection",
    "buyList":baselingk+"Order/buyList",
    "commitBuy":baselingk+"Order/commitBuy",
    "getSeed":baselingk+"Order/getSeed",
    "seedTofruit":baselingk+"Order/seedTofruit",
    "seedList":baselingk+"Order/seedList",
    "getFruit":baselingk+"Sys/getFruit",
    "buyCode":baselingk+"Log/buyCode",
    "friendGarden":baselingk+"Garden/friendGarden",
    "pick":baselingk+"Garden/pick",
    "getUser":baselingk+"Center/getUser",
    "editUser":baselingk+"Center/editUser",
    "editPwd":baselingk+"Center/editPwd",
    "mail":baselingk+"Index/add",
    "buyHoney":baselingk+"Log/buyHoney",
    "isBuyHoney":baselingk+"Garden/isBuyHoney",
    "getFruit1":baselingk+"Sys/getFruit1",
    "pickAll":baselingk+"Garden/pickAll",
    "logout":baselingk+"Login/logout",
    "Garden":baselingk+"Garden/index",
    "open":baselingk+"Garden/open",
    "sow":baselingk+"Garden/sow",
    "fertilize":baselingk+"Garden/fertilize",
    "harvest":baselingk+"Garden/harvest",
    "cai":baselingk+"Log/harvest",


  };

  function geturllink (){
    return urldate;
  }
  function login(){
    layer.open({
      content: "你的账号在另一地点登录，你被迫下线",
      skin: 'msg',
      time: 2, //2秒后自动关闭
      end:function(){
        location.href="login.html";
      }
    });
  }
  function login1(){
    layer.open({
      content: "你的账号在另一地点登录，你被迫下线",
      skin: 'msg',
      time: 2, //2秒后自动关闭
      end:function(){
        location.href="login.html";
      }
    });
  }



