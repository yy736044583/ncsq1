		//适配
		(function(doc, win) {
		    var docEl = doc.documentElement,
		        resizeEvt = 'orientationchange' in window ? 'orientationchange' : 'resize',
		        recalc = function() {
		            var clientWidth = docEl.clientWidth;
		            if (!clientWidth) return;
		            if (clientWidth >= 750) {
		                docEl.style.fontSize = '100px';
		            } else {
		                docEl.style.fontSize = 100 * (clientWidth / 750) + 'px';
		            }
		        };
		
		    if (!doc.addEventListener) return;
		    win.addEventListener(resizeEvt, recalc, false);
		    doc.addEventListener('DOMContentLoaded', recalc, false);
		})(document, window);
		//适配
//window.onload = function(){
//	$(".container-index").fadeIn(200);
//	$(".container").fadeIn(200);
//	$(".loading").hide();
//}

//留言验证
function alertA(str){
	var Html = '<div class="js_dialog" id="iosDialog2" style="opacity: 1;"><div class="weui-mask"></div><div class="weui-dialog"><div class="weui-dialog__bd">'+str+'</div><div class="weui-dialog__ft"><a href="javascript:;" class="weui-dialog__btn weui-dialog__btn_primary">知道了</a></div></div></div>'
	$("body").append(Html);
	$("#iosDialog2").fadeIn(100);
	$(".weui-dialog__btn").on('click', function(e){
		e.stopPropagation();
		$("#iosDialog2").remove()
//		$("#iosDialog2").remove();
	});
}

//  ========== 
//  = 取消预约 = 
//  ========== 


var my_yys = (function(){
	var DIVs = null;	
	return function(){
		if(!DIVs){
			DIVs =       '<div class="js_dialog" id="iosDialog" style="display:none">';
			DIVs +=           '<div class="weui-mask"></div>';
			DIVs +=            '<div class="weui-dialog">';
			DIVs +=                '<div class="weui-dialog__hd"><strong class="weui-dialog__title">提示</strong></div>';
			DIVs +=                '<div class="weui-dialog__bd">您确定要取消预约吗？</div>';
			DIVs +=                '<div class="weui-dialog__ft">';
			DIVs +=                    '<a href="javascript:;" class="weui-dialog__btn weui-dialog__btn_default" id="yy_error">取消</a>';
			DIVs +=                   '<a href="javascript:;" class="weui-dialog__btn weui-dialog__btn_primary" id="yy_success">确定</a>';
			DIVs +=                '</div>';
			DIVs +=            '</div>';
			DIVs +=       '</div>';
			$('body').append(DIVs);
		}
		return DIVs;
	}
})();


//  ========== 
//  = 预约协议展开更多 = 
//  ========== 

function moreS(that){
	var Html = $(".ph-text").html();
	if($(that).text() == "收起"){
		$(that).text("查看完整协议");
		$(".ph-text").css("height",null);
	}else{
		$(that).text("收起");
		$(".ph-text").css("height","auto");
	}
	
}

//  ========== 
//  = 填写个人信息验证 = 
//  ========== 
function postY(){
	var reg = /(^\d{15}$)|(^\d{18}$)|(^\d{17}(\d|X|x)$)/;
	if($(".name-yy").val() == ""){
		alert("请问您怎么称呼");
		$(".name-yy").focus();
		return false;
	}
	if($(".tel-yy").val() == ""){
		alert("手机号不能为空");
		$(".tel-yy").focus();
		return false;
	}
	if(!(/^1(3|4|5|7|8)\d{9}$/.test($(".tel-yy").val()))){
		alert("请输入有效手机号");
		$(".tel-yy").focus();
		return false;
	}
	if($(".tel-idcard").val() == ""){
		alert("身份证号不能为空");
		$(".tel-idcard").focus();
		return false;
	}
	if(!(reg.test($(".tel-idcard").val()))){
		alert("请输入有效身份证号");
		$(".tel-idcard").focus();
		return false;
	}
	return true;
}
