<?php
	Include_once("includes/security.php");//�����û���⺯��
	Include_once("includes/comm.php");//�����û���⺯��
	Include_once("includes/ReportComm.php");//�����û���⺯��

	$SaveExeclFileName=Chop($_POST['SaveExeclFileName']);
	
	if ($SaveExeclFileName!="")
	{
		if ($_FILES["ExeclFile"]["error"] > 0)
		{
				 	if ($_FILES["ExeclFile"]["error"]==1) $ShowStr=$ShowStr."δ�ϴ��ɹ����������1������php��С���ƣ�\\r";
				 	if ($_FILES["ExeclFile"]["error"]==2) $ShowStr=$ShowStr."δ�ϴ��ɹ����������2������html��С���ƣ�\\r";
				 	if ($_FILES["ExeclFile"]["error"]==3) $ShowStr=$ShowStr."δ�ϴ��ɹ����������3�������ϴ�ʧ�ܣ�\\r";
				 	if ($_FILES["ExeclFile"]["error"]==4) $ShowStr=$ShowStr."δ�ϴ��ɹ����������4��δָ���ļ�����\\r";
				 	if ($_FILES["ExeclFile"]["error"]==6) $ShowStr=$ShowStr."δ�ϴ��ɹ����������6���Ҳ�����ʱ�ļ��У�\\r";
				 	if ($_FILES["ExeclFile"]["error"]==7) $ShowStr=$ShowStr."δ�ϴ��ɹ����������7���ļ�д��ʧ�ܣ�\\r";
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
					//echo "�ļ��ϴ��ɹ���";
					$ShowStr=$ShowStr."[".$SaveExeclFileName."���ϴ��ɹ���\\r";
				}
				else
				{
					//echo "�ļ��ϴ�ʧ�ܣ�";
					$ShowStr=$ShowStr."[".$SaveExeclFileName."�ݿ���ʧ�ܣ�\\r";
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

<HTML><HEAD><TITLE><?php $Title=Chop($_GET['Title']); if ($Title!="") echo $Title;else echo "�鿴�ļ�"; ?></TITLE>
<META http-equiv=Content-Type content="text/html; charset=gb2312">
<LINK href="style.css" type=text/css rel=stylesheet>
<SCRIPT src="new_main.js" type=text/javascript></SCRIPT>
<!-- --------------------=== ����Weboffice��ʼ������ ===--------------------- -->
<SCRIPT language=javascript event=NotifyCtrlReady for=WebOffice1>
/****************************************************
*
*	��װ����Weboffice(ִ��<object>...</object>)
*	�ؼ���ִ�� "WebOffice1_NotifyCtrlReady"����
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
*		�ؼ���ʼ��WebOffice����
*
****************************************************/
function WebOffice1_NotifyCtrlReady() {

	document.all.WebOffice1.OptionFlag |= 128;
	// �½��ĵ�
	//document.all.WebOffice1.LoadOriginalFile("", "doc");
	//spnWebOfficeInfo.innerText="----   �������ϰ�װ��WebOffice�汾Ϊ:V" + document.all.WebOffice1.GetOcxVersion() +"\t\t\t��ʵ���Ǹ��ݰ汾V6044��д";
	
	//���ع�����
	var webObj=document.getElementById("WebOffice1");
	webObj.ShowToolBar =  !webObj.ShowToolBar;
	
	//document.all.WebOffice1.LoadOriginalFile("http://192.168.0.112:8076/shuangliu/Temp/192.168.0.78/����֪ͨ.xls", "xls");
<?php

	$ExeclFileName=Chop($_GET['ExeclFileName']);

	$fileName=basename($ExeclFileName);
	
		//�����û���ʱ�ļ�
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
*		����office�¼�������
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
			alert("���ĵ��Ѿ���ֹ����");
		}else{
			document.all.WebOffice1.lContinue = 1;
		}
	}else if(eventname=="DocumentBeforePrint"){
		if(vNoPrint){
			document.all.WebOffice1.lContinue = 0;
			alert("���ĵ��Ѿ���ֹ��ӡ");
		}else{
			document.all.WebOffice1.lContinue = 1;
		}
	}else if(eventname=="WindowSelectionChange"){
		if(vNoCopy){
			document.all.WebOffice1.lContinue = 0;
			//alert("���ĵ��Ѿ���ֹ����");
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
  <input type="submit" name="SaveFile" value="���浽������" onClick="return UpFile()">
  <input type="submit" name="QuitWindow" value="�������˳��༭" onClick="CloseWindow()">
</form>
    	<!-- -----------------------------== װ��weboffice�ؼ� ==--------------------------------- -->
      <SCRIPT src="new_LoadWebOffice.js"></SCRIPT>
			<!-- --------------------------------== ����װ�ؿؼ� ==----------------------------------- -->

<SCRIPT language=javascript>
	function UpFile()
	{
		var returnValue; // ����ҳ��ķ���ֵ
		document.all.WebOffice1.HttpInit(); // ��ʼ��Http ����
		// �����Ӧ��Post Ԫ��
<?php
		$ExeclFileName=str_replace('\\','\\\\',$ExeclFileName);
		echo "document.all.WebOffice1.HttpAddPostString(\"SaveExeclFileName\", \"$ExeclFileName\");"
?>
		// ����ϴ��ļ�
		document.all.WebOffice1.HttpAddPostCurrFile("ExeclFile","");
		// �ύ�ϴ��ļ�
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
