<?php 

require_once("mysql.class.php"); 



$email_table = "email_091016";
$limit = 900;

$server = "noreply.waiwai.com.au";


$body = GetPage("http://waiwai.com.au/m/2009/1016/mail.html");
$title = "喂喂 What's On - Finding Cover Girl 尋找封面女郎, CG Night @ Havana Club - waiwai.com.au";


//SendMail($title,$body,'zhen.wang@waiwai.com.au',"",$server,true);
//exit;


$db = new Database("02.waiwai.com.au:17909","waiwai_uc","W2jd3H7vF9U5","waiwai_uc");

$db->query("insert into {$email_table}(email,uid,username) SELECT email,uid,username  FROM `waiwai_uc`.`uc_members` group by email having uid not in(select uid from {$email_table})
");



$results =  $db->get_all("select uid,email from $email_table where status=0 limit $limit");

$count = 0;
foreach($results as $result)
{
	$count++;
	if(SendMail($title,$body,$result['email'],"",$server,true))
	{
		//update sql
		$results =  $db->update("update {$email_table} set status=1 where uid={$result['uid']}");
	    echo $result['uid'].':'.$result['email'].":success($count) \r\n ";
	}
	else
	{
		$results =  $db->update("update {$email_table} set status=2 where uid={$result['uid']}");
	    echo $result['uid'].':'.$result['email'].":fail($count) \r\n ";
	} 
}


/*
$title:郵件標題
$body:郵件正文
$add_address:收件人地址（添加多個用逗號隔開）
$add_bcc:暗抄送（添加多個用逗號隔開）
$ishtml:是否發html文件
*/
function SendMail($title,$body,$add_address,$add_bcc,$server,$ishtml=false)
{
	require_once 'class.phpmailer.php';

	//設置郵件
	$mail             = new PHPMailer();
	$mail->IsSMTP(); // telling the class to use SMTP
	$mail->SMTPAuth   = true;                  // enable SMTP authentication
    $mail->SMTPDebug  = 0;                     // enables SMTP debug information (for testing)

	
	$mail->Host       = $server;                 // sets the SMTP server
	$mail->Port       = 587;                    // set the SMTP port for the GMAIL server
	$mail->Username   = "do-not-reply@noreply.waiwai.com.au"; // SMTP account username
	$mail->Password   = "492U97";        // SMTP account password

	$mail->SetFrom('noreply@noreply.waiwai.com.au', 'waiwai.com.au');
	$mail->AddReplyTo("noreply@noreply.waiwai.com.au","waiwai.com.au");

	
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
	foreach ($add_address as $value_address)
	{
		$mail->AddAddress($value_address, "");
	}

	if(!empty($add_bcc))
	{
		$add_bcc = explode(",",$add_bcc);
		foreach ($add_bcc as $value_bcc)
		{
			$mail->AddBCC($value_bcc, "");
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



























