$(document).ready(function(){
	setC();
	setUrl();

    if($("#c option:selected").val() !== null){
        setA($("#c option:selected").val());
        if($("#c option:selected").val() !== null){
            setP($("#c option:selected").val(),$("#a option:selected").val());
        }
    }

	function setUrl(){
		$u = $("#loadAPI-Base-URL");
		for(var i=0;i<API_Base_URLS.length;i++){
			$u.append('<option value ="'+ API_Base_URLS[i] +'">'+ API_Base_URLS[i] +'</option>');
		}
	}

	function setC(){
		var $c = $("#c").html('<option id="C" value ="'+ null +'">请选择控制器</option>');
		var $a = $("#a").html('<option id="A" value ="'+ null +'">请选择执行器</option>');
		var cmap = {};
		for(var i=0;i<API_Doc.length;i++){
			cmap[API_Doc[i].parameter.c] = API_Doc[i].name;
		}
		var controller=GetQueryString('controller');
		for(var k in cmap){
			if(controller === k){
				$c.append('<option id="C" value ="'+ k +'" selected="selected">'+ cmap[k] +'</option>');
			}else{
				$c.append('<option id="C" value ="'+ k +'">'+ cmap[k] +'</option>');
			}
		}
	}

	function setA(c){
		var $a = $("#a").html('<option id="A" value ="'+ null +'">请选择执行器</option>');
		var amap = {};
		var api_url = {};
		for(var i=0;i<API_Doc.length;i++){
			if(API_Doc[i].parameter.c === c){
				amap[API_Doc[i].parameter.a] = API_Doc[i].explan;
				api_url[API_Doc[i].parameter.a] = API_Doc[i].path;
			}
		}

		var action=GetQueryString('action');
		for(var k in amap){
			if(action === k){
				$a.append('<option id="A" value ="'+ k +'" selected="selected" data="'+ api_url[k]+'">'+ amap[k] +'</option>');
			}else{
				$a.append('<option id="A" value ="'+ k +'" data="'+ api_url[k]+'">'+ amap[k] +'</option>');
			}
		}
	}

	function setP(c,a){
		for(var i=0;i<API_Doc.length;i++){
			if(API_Doc[i].parameter.c === c && API_Doc[i].parameter.a === a){
				$("#explan").html(API_Doc[i].explan + "   >   "+ API_Doc[i].method + "   >   "+ API_Doc[i].path);
				var api = API_Doc[i];
				var $pbox = $("#p-box");

				for(var k in API_Doc[i].parameter){
					if(k === "d" || k === "c" || k === "a"){
						continue;
					}else{
						if(k === "token" || k === "timestamp" || k === "sign"){
							continue;
						}
						var $p = $("<input/>").attr("name",k).attr("value",null).attr("placeholder",API_Doc[i].parameter[k]);
						var $tr = $("<tr></tr>");
						var $m = $("<a class='pick' data-t='d'>禁用</a>");
						$("<td>"+ k +"</td>").appendTo($tr);
						$("<td></td>").append($p).appendTo($tr).attr("class","p-"+k);
						$("<td></td>").append($m).appendTo($tr);
						$pbox.append($tr);
					}
				}
			}
		}
	}

	//点击删除--删除参数或加入参数
	$(document).on("click",".pick",function(){
		if($(this).data("t") === "d"){
			$(this).data("t","s");
            $(this).parents("tr").find("input").attr("disabled",true);
            $(this).parents("tr").find("a").text("启用");
		}else{
			$(this).data("t","d");
            $(this).parents("tr").find("input").attr("disabled",false);
            $(this).parents("tr").find("a").text("禁用");
		}
	});

    //变更选择的控制器
	$("#c").change(function(){
		$("#explan").html("");
		$("#p-box").html("");
		$("#api_iframe").html("");
		setA($("#c option:selected").val());
	});

	//变更选择的执行器
	$("#a").change(function(){
		$("#explan").html("");
		$("#p-box").html("");
		$("#api_iframe").html("");
		setP($("#c option:selected").val(),$("#a option:selected").val());
	});

	$("#POST-BTN").click(function(){
		//$("#form-box form").attr("method","post");
		//$("#form-box form").attr("action","http://"+$("#loadAPI-Base-URL option:selected").val() + $("#a option:selected").attr('data'));
		//$("#form-box form").submit();

        //请求接口
        request_ajax("POST");
	});

	$("#GET-BTN").click(function(){
		//$("#form-box form").attr("method","get");
		//$("#form-box form").attr("action","http://"+$("#loadAPI-Base-URL option:selected").val() + $("#a option:selected").attr('data'));
		//$("#form-box form").submit();

		//请求接口
        request_ajax("GET");
	});

	//请求接口
	function request_ajax(method){
        //获取接口地址--不含域名和参数
        var api_url_center = get_api_url_center();

        //获取GET提交过来的参数
        var biz_params = get_all_parameter(method,api_url_center);

        //组装要请求的接口地址
        var url = get_url(api_url_center);

        //获取token
        var token = get_token();

        //请求接口
        ajax(method,biz_params,token,url,api_url_center);
	}

	//获取接口地址--不含域名和参数
	function get_api_url_center(){
        var api_url = $("#a option:selected").attr('data');
        var api_url_first = api_url.substring(0, 1);
        var api_url_center = "";
        if(api_url_first === "/"){
            api_url_center = api_url.substring(1);
        }else{
            api_url_center = api_url;
        }

        return api_url_center;
	}

	//获取token
	function get_token(){
        var token = "";
        if($.cookie("token")){
            token = $.cookie("token");
        }
        return token;
	}

	//组装要请求的接口地址
	function get_url(api_url_center){
		//获取域名
        var domain = window.location.host;

        var url = "";
        if(domain === "test.changrentech.com" || domain === "https://test.changrentech.com" || domain === "http://test.changrentech.com"){
            url = "https://";
        }else{
            url = "http://";
        }
        url += domain + "/" + api_url_center;

        return url;
	}

	//ajax请求接口
	function ajax(method,biz_params,token,url,api_url_center){
        $.ajax({
            type: method,
            url: url,
            data: biz_params,
            beforeSend: function(request) {
                request.setRequestHeader("token", token);
            },
            success: function (data) {
                //将json字符串转json对象
                var data_obj = eval('(' + data + ")")
                //判断请求是否成功并且该请求是登录操作，如果都满足就将请求回来的token写入cookie
                if(data_obj.code === 0 && api_url_center === "v1/user/login"){
                    $.cookie("token",data_obj.data.token);
                }
                //格式化字符串
                var mes=JSON.stringify(data_obj, null, 4);
                //将返回的json格式化输出显示到textarea框内
                $("#api_iframe").html("<textarea style='width: 100%;min-height: 720px;overflow: auto; border:solid 0px #000;'>" + mes + "</textarea>");
            },
            error:function(res){
                alert(res.statusText);
            }
        });
	}

	//生成签名
	function get_sign(reqMethod,reqData,pathinfo){
		var sign = {
			toUnicode: function (s) {
				return s.replace(/([\u4E00-\u9FA5]|[\uFE30-\uFFA0])/g, function (newStr) {
					return "\\u" + newStr.charCodeAt(0).toString(16);
				});
			},
			sortArray: function (arys) {
				var newkey = Object.keys(arys).sort();
				var newObj = {};
				for(var i = 0; i < newkey.length; i++) {
						newObj[newkey[i]] = arys[newkey[i]];
				}
				return newObj;
			},
			buildParams: function (obj) {
				const params = [];
				for (var key in obj){
					var value = obj[key];
					if (typeof value === 'undefined') {
							value = '';
					}
					params.push([key, value].join('='));
				}
				return params.join('&');
			},
			run: function (signKey) {
				// 密钥
				if (typeof signKey === "undefined") {
					signKey = '#@%^&';
				}
				var self = this;

				// 公共参数初始化
				var timestamp = Math.round(new Date().getTime()/1000);
				var source = reqData["source"];
				if (source === "undefined") {
					source = 'app';
				}

				// 字典排序请求参数列表
				var array = {};
				if (Object.getOwnPropertyNames(reqData).length > 0) {
					for (var key in reqData) {
						// 过滤头像参数
						if (reqData[key] !== '' && key !== 'avatar' && key !== 'sign' && key !== 'timestamp' && key !== 'source') {
							array[key] = reqData[key];
						}
					}
				}
			    // 手动加入公共参数
				array['timestamp'] = timestamp;
				array['source'] = source;
				var sorted = self.sortArray(array);
				var params = self.buildParams(sorted);
				var code = reqMethod + '&' + pathinfo + '?' + params;

				// 加密sha1->base64
				var sha1_string = CryptoJS.HmacSHA1(code, signKey).toString();
				var encode_string = CryptoJS.enc.Utf8.parse(sha1_string);
				var sign_str =  CryptoJS.enc.Base64.stringify(encode_string).toString();
				var par = {};
				par['sign'] = sign_str;
				par['timestamp'] = timestamp;

				return par;
			}
		};

		return sign.run('#@%^&');
	}

	//获取所有POST/GET提交的值
	function get_all_parameter(method,api_url_center) {
		var arr = {};
        var obj = $("#p-box input").serializeArray();
        for (var i in obj) {
        	arr[obj[i].name] = obj[i].value;
            /*arr.push(obj[i]);*/
        }
        /*
		$("#p-box input").each(function () {
			var name = $(this).attr("name");
			var value = $(this).val();
			params[name] = value;
		});
		*/
        //生成签名
        var par = get_sign(method,arr,api_url_center);
        arr['sign'] = par['sign'];
        arr['timestamp'] = par['timestamp'];
        /*
        params.push({name:'sign',value:par['sign']});
        params.push({name:'timestamp',value:par['timestamp']});
        */
		return arr;
	}

	//获取url参数
    function GetQueryString(name){
        var reg = new RegExp("(^|&)"+ name +"=([^&]*)(&|$)");
        var r = window.location.search.substr(1).match(reg);
        if(r !== null)
            return  unescape(r[2]);
        return null;
    }
});