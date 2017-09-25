//加密
function jiami(jsontextall){
    var resultlastasd;
    //加密

    //sha1
    var sha1Sync_value = $.sha1(jsontextall);

    //转换小写
    sha1Sync_value = sha1Sync_value.toLowerCase();
    //添加秘钥
    var jsontextall_mi = sha1Sync_value + '#l_vle_ll_e%+$^@0608)[';
    //md5
    var md5Sync_value = $.md5(jsontextall_mi);

    //转换小写
    resultlastasd = md5Sync_value.toLowerCase();
    return resultlastasd;
}


//生成客户端签名
function signaturetik(){
    var resultlast;
    var jsontext = [];
    var args = arguments;
    if (args){
        for(var i = 0;i<args.length;i++){
            jsontext.push(args[i]);
        }
    }

    jsontext.sort(); //排序

    var jsontextall = jsontext.join('&');  //拼接

    resultlast = jiami(jsontextall);

    return resultlast;

}


//随机数生成
function tokenmake(){
    var tmp = Date.parse( new Date() ).toString();
    tmp = tmp.substr(0,10);
    return tmp;
}

//生成带键值的字符串
function addname(namen,vuln){
    var valname = namen.toString() + vuln.toString();
    return valname;

}







//json数组获取特定子元素
function arrdata_ch(arrdate,arrval){

    for(var i=0;i<arrdate.length;i++){
        if (arrdate[i].landId==arrval){
            return arrdate[i];
        }

    }
}


