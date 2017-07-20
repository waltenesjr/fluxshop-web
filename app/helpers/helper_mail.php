<?php
require_once HELPERDIR . "phpmailer/class.phpmailer.php";
global $mail;
$mail = new PHPMailer();
$mail->SMTPSecure = "tls";
$mail->IsSMTP();
$mail->SMTPAuth = true;
$mail->WordWrap = 80;
$mail->IsHTML( true );
?>