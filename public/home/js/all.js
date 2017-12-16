$(function(){
	//获取时间
	setInterval(function() {
		var mytime = new Date();
		var Year = mytime.getFullYear();
		var Month = mytime.getMonth() + 1;
		var Dates = mytime.getDate();
		var Hours = mytime.getHours();
		
		var Minutes = mytime.getMinutes();
		var Seconds = mytime.getSeconds();
		if (Minutes<10) {
			Minutes = '0'+Minutes;
		}
		if (Seconds<10) {
			Seconds = '0'+Seconds;
		}
		var timehtml = Year+"年"+Month+"月"+Dates+"日"+Hours+":"+Minutes+":"+Seconds;
	    $('.header-time').text(timehtml);
	}, 1000);
});

function alertS(str){
	var aleHtml = '<div id="alert" style="position: fixed;z-index: 9999;width: 400px; height: 250px; background-color: rgba(255,255,255,0.9); border-radius: 4px;left: 50%;top: 50%;margin-left: -200px;margin-top:-135px;overflow: hidden;">'
		aleHtml	+= '<div id="alert-text" style="display: table;height: 200px;width: 100%;">'
		aleHtml	+=	'<p style="display: table-cell; vertical-align: middle;text-align: center;font-size: 20px; color: #333333;padding: 0 15px;">'+str+'</p>'
		aleHtml	+= '</div>'
		aleHtml	+= '<div id="alert-lick" style="height: 50px; background-color: #00cdff;text-align: center;font-size: 20px;color: #FFFFFF;width: 100%;line-height: 50px;">我知道了</div>'
	aleHtml	+= '</div>'
	aleHtml	+= '<div id="bg"style="position: fixed;z-index: 9998;background-color: #000000;opacity: 0.5;left: 0;top: 0;right: 0;bottom: 0;"></div>';
		$('body').append(aleHtml);
		$("#alert-lick").click(function(e){
			e.stopPropagation();
			$("#alert, #bg").remove();
		})
}
