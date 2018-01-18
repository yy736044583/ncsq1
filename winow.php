<?php 
	$id = $_GET['area'];
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title></title>
	<script type="text/javascript" src="/ncsq/public/take1/jquery1.42.min.js"></script>
	<style type="text/css">
		*{margin: 0;padding: 0;}
		body{width: 640px;background: #000;}
		.ck-list{height: 42.66666px;width: 100%;border-bottom: 1px solid #f5f5f5;color: red;text-align: center;line-height: 42.66666px;font-size: 16px}
	</style>

</head>
<body id="body-text">
	<script type="text/javascript">
		$.ajax({
			url: '/ncsq/winled/index?action=showque&number=<?php echo $id; ?>',
			type: 'GET',
			dataType: 'json',
			success:function(data){
				if(data.code == 200){					
					for(var i=0;i<=data.data.length-1;i++){
						var Html = '<div class="ck-list key'+i+'"></div>';
						$("#body-text").append(Html);
					}
				}
			}
		})		
		setInterval(function(){
			$.ajax({
				url: '/ncsq/winled/index?action=showque&number=<?php echo $id; ?>',
				type: 'GET',
				dataType: 'json',
				data:{ran:Math.round(Math.random()*10)},
				cache:false,
				success:function(data){
					if(data.code == 200){					
						for(var i=0;i<=data.data.length-1;i++){
							if (data.data[i].online == 0 || data.data[i].online == 2 || data.data[i].online == 3) {
								$(".key"+i).text('欢迎您的光临');
							}else{
								if(data.data[i].queue == null || data.data[i].queue == ""){
									$(".key"+i).text('欢迎您的光临');
								}else {
									$(".key"+i).text('请'+data.data[i].queue+'办理业务');
								}
								
							}
						}
					}
				}
			})
		}, 1000);
	</script>
</body>
</html>