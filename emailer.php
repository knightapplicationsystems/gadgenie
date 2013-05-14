<?php

require('lib/class.phpmailer.php');
require('lib/class.pop3.php');
require('lib/class.smtp.php');

function send_new_add_stock_email($email, $mailType,$url) {
    $mail = new PHPMailer();
    $mail->IsSMTP();

    //GMAIL config
    $mail->SMTPAuth = true; 
    $mail->Mailer = 'smtp';// enable SMTP authentication
    //$mail->SMTPSecure = "ssl";                 // sets the prefix to the server
    $mail->Host = "gadgenie.com";      // sets GMAIL as the SMTP server
    $mail->Port = 25;                   // set the SMTP port for the GMAIL server
    $mail->Username = "noreply@gadgenie.com";  // GMAIL username
    $mail->Password = "522561jh";            // GMAIL password
    //End Gmail

    $mail->From = "noreply@gadgenie.com";
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
    $mail->Send();

        
}



function send_password_reminder($email,$mailType,$unique_ref)
{
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
				Your password has been changed to $unique_ref.
                                <br>
                                Please login and change your password
				</body>
				</html>

				");

    $mail->AddAddress($email);
    $mail->AddBCC('justin@knightfinderapp.com', 'Justin Howard');
    $mail->IsHTML(true); // send as HTML
    $mail->Send();
}

?>