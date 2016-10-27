<?php 
define('VALID_HOSTS', "/^https?:\/\/(([^\/\.]+\.)*waiwai\.com\.au|localhost|127\.0\.0\.1)\/.*/i");		// 允許的站點
define('ERROR_MESSAGE', 'Service Unavailable');											// 錯誤訊息
if($_SERVER['HTTP_REFERER'] != '' && !preg_match(VALID_HOSTS, $_SERVER['HTTP_REFERER'])) { showError(); }


$body = GetPage("content.html");
$body = 'hello, test!';



require_once("mysql.class.php"); 
$db = new Database("localhost:17909","root","XnBi2jrUdfI9","test")
$results =  $db->get_all("select id,email from user where status=0 limit 20");
$email = "";
$id = "";
foreach($results as $result)
{
	$email .= ','.$result['email'];
	$id = ','.$result['id'];
}
if(empty($email))
{
	exit '0|找不到需要發送的郵件地址。';
}

$email = substr($email,1);
$id = substr($id,1);

//var_dump(SendMail("test #1",$body,"zhen.wang@waiwai.com.au","",true));
if(SendMail("test #1",$body,$email,"",true))
{
	//update sql
	$results =  $db->update("update user set status=1 where id in($id)");

	$echo '1|'.$email;
}
else
{
	exit '0|發送失敗。';
}


/*
$title:郵件標題
$body:郵件正文
$add_address:收件人地址（添加多個用逗號隔開）
$add_bcc:暗抄送（添加多個用逗號隔開）
$ishtml:是否發html文件
*/
function SendMail($title,$body,$add_address,$add_bcc,$ishtml=false)
{
	require_once 'class.phpmailer.php';

	//設置郵件
	$mail             = new PHPMailer();
	$mail->IsSMTP(); // telling the class to use SMTP
	$mail->SMTPAuth   = true;                  // enable SMTP authentication
    $mail->SMTPDebug  = 2;                     // enables SMTP debug information (for testing)

/*

	$mail->Host       = "ssl://smtp.gmail.com:465"; // sets the SMTP server
	$mail->Port       = 465;                    // set the SMTP port for the GMAIL server
	$mail->Username   = "noreply@waiwai.com.au"; // SMTP account username
	$mail->Password   = "492U97";        // SMTP account password

*/

	
	$mail->Host       = "noreply.waiwai.com.au"; // sets the SMTP server
	$mail->Port       = 587;                    // set the SMTP port for the GMAIL server
	$mail->Username   = "do-not-reply@noreply.waiwai.com.au"; // SMTP account username
	$mail->Password   = "492U97";        // SMTP account password



/*
	$mail->Host    = "s2smtpout.secureserver.net"; 
	$mail->Port       = 25;                    
	$mail->Username   = ""; 
	$mail->Password   = ""; 
*/	

	
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

function showError()
{
	die(ERROR_MESSAGE);
}



?>



























