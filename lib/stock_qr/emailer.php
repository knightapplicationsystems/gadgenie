<?php

require('class.phpmailer.php');
require('class.pop3.php');
require('class.smtp.php');

function send_new_add_stock_email($email, $mailType,$url) {
    $mail = new PHPMailer();
    $mail->IsSMTP();

    //GMAIL config
    $mail->SMTPAuth = true; 
    $mail->Mailer = 'smtp';// enable SMTP authentication
    $mail->SMTPSecure = "ssl";                 // sets the prefix to the server
    $mail->Host = "smtp.gmail.com";      // sets GMAIL as the SMTP server
    $mail->Port = 465;                   // set the SMTP port for the GMAIL server
    $mail->Username = "justin@knightfinderapp.com";  // GMAIL username
    $mail->Password = "522561jh";            // GMAIL password
    //End Gmail

    $mail->From = "support@gadgenie.com";
    $mail->FromName = "Gad Genie Support";
    $mail->Subject = $mailType;
    $mail->MsgHTML
            ("

				<html>
				<body>
				Go to $url                       
				</body>
				</html>

				");

    $mail->AddAddress($email);
    $mail->AddBCC('justin@knightfinderapp.com', 'Justin Howard');
    $mail->IsHTML(true); // send as HTML
    if(!$mail->Send()) {
		$error = 'Mail error: '.$mail->ErrorInfo; 
		return false;
	} else {
		$error = 'Message sent!';
		return true;
	}
        echo 'here';
}

?>