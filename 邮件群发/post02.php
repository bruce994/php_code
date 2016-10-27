<?php 

require_once("mysql.class.php"); 


$email_table = "email_091016";
$limit = 100;

$server = "03.waiwai.com.au";
$username   = "do-not-reply@noreply.waiwai.com.au"; 
$port = 587;
$password   = "K3u5eM8v";      

/*
$server = "02.noreply.waiwai.com.au";
$username   = "do-not-reply@noreply.02.waiwai.com.au"; 
$port = 587;
$password   = "492U97";       
*/

$body = GetPage("http://waiwai.com.au/m/2010/0505/mail.html");
$title = "喂喂 What's On - 五月天悉尼演唱會門票最後搶票機會，就在喂喂網";

$db = new Database("02.waiwai.com.au:17909","waiwai_uc","vm3hFncBi2DS","waiwai_uc");

//var_dump(SendMail($title,$body,'zhen.wang@waiwai.com.au',"wang","","",$server,$port,$username,$password,true));

//var_dump(SendMail($title,$body,'kenyo.wu@waiwai.com.au,renee.deng@waiwai.com.au,zhen.wang@waiwai.com.au',"kenyo,renee,wang","","",$server,$port,$username,$password,true));
//exit;


$db->query("insert into {$email_table}(email,uid,username) SELECT email,uid,username  FROM `waiwai_uc`.`uc_members` group by email having uid not in(select uid from {$email_table})");


$results =  $db->get_all("select uid,username,email from $email_table where status=0 limit $limit");

$i = 0;
//0未發送,1成功,2失敗,3退訂
foreach($results as $result)
{
	$time = date('Ymd H:i:s');
	if(SendMail($title,$body,$result['email'],$result['username'],"","",$server,$port,$username,$password,true))
	{
		//update sql
		$results =  $db->update("update {$email_table} set status=1,updatetime='{$time}' where uid={$result['uid']}");
	    echo $result['uid'].':'.$result['email'].":success \r\n ";
	}
	else
	{
		$results =  $db->update("update {$email_table} set status=2,updatetime='{$time}' where uid={$result['uid']}");
	    echo $result['uid'].':'.$result['email'].":fail \r\n ";
		$i++; //如果連續失敗退出
		if($i>20)
		{
			break;
		}
	} 

	usleep(250000);  //0.25 秒
}


/*
$title:郵件標題
$body:郵件正文
$add_address:收件人地址（添加多個用逗號隔開）
$add_name:收件人名（添加多個用逗號隔開,于$add_address一一對應,爲空可用""）
$add_bcc:暗抄送（添加多個用逗號隔開）
$bcc_name:暗抄送名（添加多個用逗號隔開,于$add_bcc一一對應,爲空可用""）
$server
$port
$username
$password
$ishtml:是否發html文件
*/
function SendMail($title,$body,$add_address,$add_name,$add_bcc,$bcc_name,$server,$port,$username,$password,$ishtml=false)
{
	require_once 'class.phpmailer.php';

	//設置郵件
	$mail             = new PHPMailer();
	$mail->IsSMTP(); // telling the class to use SMTP
	$mail->SMTPAuth   = true;                  // enable SMTP authentication
    $mail->SMTPDebug  = 1;                     // enables SMTP debug information (for testing)

	
	$mail->Host       = $server;                 
	$mail->Port       = $port;                   
	$mail->Username   = $username;
	$mail->Password   = $password;

	$mail->SetFrom('do-not-reply@noreply.waiwai.com.au', 'waiwai.com.au');
	
	$mail->Subject    = $title;
	if($ishtml)
	{
		$mail->MsgHTML($body);
	}
	else
	{
		$mail->Body = $body;  //純文本
	}

	$add_address = explode(",",$add_address);
	$add_name = explode(",",$add_name);
	foreach ($add_address as $key=>$value_address)
	{
	    $mail->AddAddress($value_address, $add_name[$key]);
	}

	if(!empty($add_bcc))
	{
		$add_bcc = explode(",",$add_bcc);
		$bcc_name = explode(",",$bcc_name);
		foreach ($add_bcc as $key=>$value_bcc)
		{
			$mail->AddBCC($value_bcc, $bcc_name[$key]);
		}
	}
	return $mail->Send();
}


function GetPage($filepath)
{
	$revalue = '';
	$filepath=trim($filepath);
	$htmlfp=@fopen($filepath,"r");
	//遠程
	if(strstr($filepath,"://"))
	{
		while($data=@fread($htmlfp,500000))
		{
			$revalue.=$data;
		}
	}
	else //本地
	{
		$revalue = fread($htmlfp, filesize ($filepath));
	}
	return $revalue;
}

?>



























