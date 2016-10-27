<?



require_once ('email.class.php');
//##########################################

$smtpserver = "noreply.02.waiwai.com.au";//SMTP服务器
$smtpserverport =587;//SMTP服务器端口
$smtpusermail = "do-not-reply@noreply.02.waiwai.com.au";//SMTP服务器的用户邮箱
$smtpemailto = "zhen.wang@waiwai.com.au";//发送给谁
$smtpuser = "do-not-reply@noreply.02.waiwai.com.au";//SMTP服务器的用户帐号
$smtppass = "492U97";//SMTP服务器的用户密码
$mailsubject = " this is test ";//邮件主题
$mailbody = "<h1> 这是一个测试程序11111 </h1>";//邮件内容
$mailtype = "HTML";//邮件格式（HTML/TXT）,TXT为文本邮件
##########################################
$smtp = new smtp($smtpserver,$smtpserverport,true,$smtpuser,$smtppass);//这里面的一个true是表示使用身份验证,否则不使用身份验证.
$smtp->debug = TRUE;//是否显示发送的调试信息
$result=$smtp->sendmail($smtpemailto, $smtpusermail, $mailsubject, $mailbody, $mailtype);
echo var_dump($result);
?>