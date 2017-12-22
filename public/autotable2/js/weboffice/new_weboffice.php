<?php
	Include_once("includes/security.php");//引用用户检测函数
	Include_once("includes/comm.php");//引用用户检测函数
	Include_once("includes/ReportComm.php");//引用用户检测函数

	$SaveExeclFileName=Chop($_POST['SaveExeclFileName']);
	
	if ($SaveExeclFileName!="")
	{
		if ($_FILES["ExeclFile"]["error"] > 0)
		{
				 	if ($_FILES["ExeclFile"]["error"]==1) $ShowStr=$ShowStr."未上传成功！错误代码1：超出php大小限制！\\r";
				 	if ($_FILES["ExeclFile"]["error"]==2) $ShowStr=$ShowStr."未上传成功！错误代码2：超出html大小限制！\\r";
				 	if ($_FILES["ExeclFile"]["error"]==3) $ShowStr=$ShowStr."未上传成功！错误代码3：部分上传失败！\\r";
				 	if ($_FILES["ExeclFile"]["error"]==4) $ShowStr=$ShowStr."未上传成功！错误代码4：未指定文件名！\\r";
				 	if ($_FILES["ExeclFile"]["error"]==6) $ShowStr=$ShowStr."未上传成功！错误代码6：找不到临时文件夹！\\r";
				 	if ($_FILES["ExeclFile"]["error"]==7) $ShowStr=$ShowStr."未上传成功！错误代码7：文件写入失败！\\r";
		}
		else
		{
//		$SaveExeclFileName=GetAppPath().$SaveExeclFileName;
//		echo $SaveExeclFileName;
//		exit;
				  if(is_file($SaveExeclFileName))
					unlink($SaveExeclFileName);
				createDir(dirname($SaveExeclFileName));   
				$TempFile=$_FILES["ExeclFile"]["tmp_name"];
				$UpOk=move_uploaded_file($TempFile,$SaveExeclFileName);
				if ($UpOk)
				{
					//echo "文件上传成功！";
					$ShowStr=$ShowStr."[".$SaveExeclFileName."］上传成功！\\r";
				}
				else
				{
					//echo "文件上传失败！";
					$ShowStr=$ShowStr."[".$SaveExeclFileName."］拷贝失败！\\r";
				}
		}

        echo "<script language=\"JavaScript\">\n";
        echo "<!--\n";
        echo "alert(\"$ShowStr\");";
        echo "// -->\n";
        echo "</script>\n";
	}
	
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">

<HTML><HEAD><TITLE><?php $Title=Chop($_GET['Title']); if ($Title!="") echo $Title;else echo "查看文件"; ?></TITLE>
<META http-equiv=Content-Type content="text/html; charset=gb2312">
<LINK href="style.css" type=text/css rel=stylesheet>
<SCRIPT src="new_main.js" type=text/javascript></SCRIPT>
<!-- --------------------=== 调用Weboffice初始化方法 ===--------------------- -->
<SCRIPT language=javascript event=NotifyCtrlReady for=WebOffice1>
/****************************************************
*
*	在装载完Weboffice(执行<object>...</object>)
*	控件后执行 "WebOffice1_NotifyCtrlReady"方法
*
****************************************************/
    //WebOffice_Event_Flash("NotifyCtrlReady");
	WebOffice1_NotifyCtrlReady()
</SCRIPT>

<SCRIPT language=javascript event=NotifyWordEvent(eventname) for=WebOffice1>
<!--
WebOffice_Event_Flash("NotifyWordEvent");
 WebOffice1_NotifyWordEvent(eventname);
 
//-->
</SCRIPT>

<SCRIPT language=javascript event=NotifyToolBarClick(iIndex) for=WebOffice1>
<!--
  WebOffice_Event_Flash("NotifyToolBarClick");
 WebOffice1_NotifyToolBarClick(iIndex);
//-->
</SCRIPT>

<SCRIPT language=javascript>
/****************************************************
*
*		控件初始化WebOffice方法
*
****************************************************/
function WebOffice1_NotifyCtrlReady() {

	document.all.WebOffice1.OptionFlag |= 128;
	// 新建文档
	//document.all.WebOffice1.LoadOriginalFile("", "doc");
	//spnWebOfficeInfo.innerText="----   您电脑上安装的WebOffice版本为:V" + document.all.WebOffice1.GetOcxVersion() +"\t\t\t本实例是根据版本V6044编写";
	
	//隐藏工具条
	var webObj=document.getElementById("WebOffice1");
	webObj.ShowToolBar =  !webObj.ShowToolBar;
	
	//document.all.WebOffice1.LoadOriginalFile("http://192.168.0.112:8076/shuangliu/Temp/192.168.0.78/收讫通知.xls", "xls");
<?php

	$ExeclFileName=Chop($_GET['ExeclFileName']);

	$fileName=basename($ExeclFileName);
	
		//建立用户临时文件
		$TempPath = GetAppPath()."Temp\\".GetIP()."\\".$fileName;
		createDir(dirname($TempPath));   
		if (is_file($TempPath))
			unlink($TempPath);
	
	$ExeclFileName=str_replace('/','\\',$ExeclFileName);
	copy($ExeclFileName ,$TempPath);
	
	$TempPath=str_replace('\\','/',$TempPath);
	$TempPath=GetHostStr().$TempPath;//$TempPath=GetHostStr()."shuangliu/".$TempPath;
	

	echo "document.all.WebOffice1.LoadOriginalFile(\"$TempPath\", \"xls\");";
?>	
}
var flag=false;
function menuOnClick(id){
	var id=document.getElementById(id);
	var dis=id.style.display;
	if(dis!="none"){
		id.style.display="none";
		
	}else{
		id.style.display="block";
	}
}
/****************************************************
*
*		接收office事件处理方法
*
****************************************************/
var vNoCopy = 0;
var vNoPrint = 0;
var vNoSave = 0;
var vClose=0;
function no_copy(){
	vNoCopy = 1;
}
function yes_copy(){
	vNoCopy = 0;
}


function no_print(){
	vNoPrint = 1;
}
function yes_print(){
	vNoPrint = 0;
}


function no_save(){
	vNoSave = 1;
}
function yes_save(){
	vNoSave = 0;
}
function EnableClose(flag){
 vClose=flag;
}
function CloseWord(){
	
  document.all.WebOffice1.CloseDoc(0); 
}

function WebOffice1_NotifyWordEvent(eventname) {
	if(eventname=="DocumentBeforeSave"){
		if(vNoSave){
			document.all.WebOffice1.lContinue = 0;
			alert("此文档已经禁止保存");
		}else{
			document.all.WebOffice1.lContinue = 1;
		}
	}else if(eventname=="DocumentBeforePrint"){
		if(vNoPrint){
			document.all.WebOffice1.lContinue = 0;
			alert("此文档已经禁止打印");
		}else{
			document.all.WebOffice1.lContinue = 1;
		}
	}else if(eventname=="WindowSelectionChange"){
		if(vNoCopy){
			document.all.WebOffice1.lContinue = 0;
			//alert("此文档已经禁止复制");
		}else{
			document.all.WebOffice1.lContinue = 1;
		}
	}else   if(eventname =="DocumentBeforeClose"){
	    if(vClose==0){
	    	document.all.WebOffice1.lContinue=0;
	    } else{
	    	//alert("word");
		    document.all.WebOffice1.lContinue = 1;
		  }
 }
	//alert(eventname); 
}
function dd(){

	document.all.WebOffice1.FullScreen=0;

}</SCRIPT>
<LINK href="./style.css" type=text/css rel=stylesheet>
<META content="MSHTML 6.00.2900.5921" name=GENERATOR></HEAD>
<BODY style="BACKGROUND: #ccc" onunload="return window_onunload()">
<form id="form1" name="form1" method="post" action="">
  <input type="submit" name="SaveFile" value="保存到服务器" onClick="return UpFile()">
  <input type="submit" name="QuitWindow" value="不保存退出编辑" onClick="CloseWindow()">
</form>
    	<!-- -----------------------------== 装载weboffice控件 ==--------------------------------- -->
      <SCRIPT src="new_LoadWebOffice.js"></SCRIPT>
			<!-- --------------------------------== 结束装载控件 ==----------------------------------- -->

<SCRIPT language=javascript>
	function UpFile()
	{
		var returnValue; // 保存页面的返回值
		document.all.WebOffice1.HttpInit(); // 初始化Http 引擎
		// 添加相应的Post 元素
<?php
		$ExeclFileName=str_replace('\\','\\\\',$ExeclFileName);
		echo "document.all.WebOffice1.HttpAddPostString(\"SaveExeclFileName\", \"$ExeclFileName\");"
?>
		// 添加上传文件
		document.all.WebOffice1.HttpAddPostCurrFile("ExeclFile","");
		// 提交上传文件
		returnValue = document.all.WebOffice1.HttpPost("./weboffice.php");
		//alert(returnValue); 
		CloseWindow();
	}
	
	function CloseWindow()
	{
		CloseWord();
		window_onunload();
	window.opener = null; 
	window.open("","_self");
	window.close();
	}

function CloseWord()
{
	
  document.all.WebOffice1.CloseDoc(0); 
}
</SCRIPT>
</BODY></HTML>
