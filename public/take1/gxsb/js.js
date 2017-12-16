	function settime(){
		var mytime = new Date();
		var clear = null;
		var Hours = mytime.getHours();
		var Minutes = mytime.getMinutes();
		// var Minutes = mytime.getSeconds();
		var Year = mytime.getFullYear();
		var Month = mytime.getMonth() + 1;
		var Dates = mytime.getDate();
		var Day = mytime.getDay();
		if(Month<10){
			Month = "0"+Month;
		}
		if(Dates<10){
			Dates = "0"+Dates;
		}
		if(Minutes<10){
			Minutes = "0"+Minutes;
		}
		if(Hours<10){
			Hours = "0"+Hours;
		}
		var week = ['星期天', '星期一', '星期二', '星期三', '星期四', '星期五', '星期六'];
		var timehtml = Hours+":"+Minutes;
		var timehtml1 = Year+"-"+Month+"-"+Dates+" "+week[Day];
		document.querySelector('p.tiem1').innerText = timehtml;
		document.querySelector('p.tiem2').innerText = timehtml1;
		mytime = null;
	}
	setInterval("settime()", 1000);