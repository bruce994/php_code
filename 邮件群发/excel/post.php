<?php
header('Content-Type:   text/html;   charset=utf-8');


$server = "03.waiwai.com.au";
$username   = "do-not-reply@noreply.waiwai.com.au"; 
$port = 587;
$password   = "K3u5eM8v";      


$body = GetPage("http://waiwai.com.au/m/2010/0505/mail.html");
$title = "喂喂 What's On - 五月天悉尼演唱會門票最後搶票機會，就在喂喂網";

     
// Test CVS


require_once 'Excel/reader.php';



// ExcelFile($filename, $encoding);
$data = new Spreadsheet_Excel_Reader();

// Set output Encoding.
$data->setOutputEncoding('utf-8');

/***
* if you want you can change 'iconv' to mb_convert_encoding:
* $data->setUTFEncoder('mb');
*
**/

/***
* By default rows & cols indeces start with 1
* For change initial index use:
* $data->setRowColOffset(0);
*
**/



/***
*  Some function for formatting output.
* $data->setDefaultFormat('%.2f');
* setDefaultFormat - set format for columns with unknown formatting
*
* $data->setColumnFormat(4, '%.3f');
* setColumnFormat - set format for column (apply only to number fields)
*
**/


$data->read('post.xls');


/*

 $data->sheets[0]['numRows'] - count rows
 $data->sheets[0]['numCols'] - count columns
 $data->sheets[0]['cells'][$i][$j] - data from $i-row $j-column

 $data->sheets[0]['cellsInfo'][$i][$j] - extended info about cell
    
    $data->sheets[0]['cellsInfo'][$i][$j]['type'] = "date" | "number" | "unknown"
        if 'type' == "unknown" - use 'raw' value, because  cell contain value with format '0.00';
    $data->sheets[0]['cellsInfo'][$i][$j]['raw'] = value if cell without format 
    $data->sheets[0]['cellsInfo'][$i][$j]['colspan'] 
    $data->sheets[0]['cellsInfo'][$i][$j]['rowspan'] 
*/

error_reporting(E_ALL ^ E_NOTICE);



for ($i = 1; $i <= $data->sheets[0]['numRows']; $i++) {
	//for ($j = 1; $j <= $data->sheets[0]['numCols']; $j++) {
		$name = $data->sheets[0]['cells'][$i][1];
		$email = $data->sheets[0]['cells'][$i][2];
		if(empty($name) || empty($email))
	    {
			continue;
		}

		//echo "Name:".$name.",Email:".$email."\n";

		if(SendMail($title,$body,$email,$name,"","",$server,$port,$username,$password,true))
	    {
			echo "Name:".$name.",Email:".$email."(T) \n ";
		}
		else
	    {
			echo "Name:".$name.",Email:".$email."(F) \n ";
		}
	//}
}



//var_dump(SendMail($title,$body,'zhen.wang@waiwai.com.au',"zhen.wang","","",$server,$port,$username,$password,true));
//exit;




function SendMail($title,$body,$add_address,$add_name,$add_bcc,$bcc_name,$server,$port,$username,$password,$ishtml=false)
{
	require_once '../class.phpmailer.php';

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




//print_r($data);
//print_r($data->formatRecords);
?>
