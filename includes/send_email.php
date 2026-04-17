<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

function sendEmail($to, $name, $subject, $message){

    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'Ijazmuteeba@gmail.com';   
        $mail->Password   = 'bjwb bqwt rmdw ecxi';     
        $mail->SMTPSecure = 'tls';
        $mail->Port       = 587;

        $mail->setFrom('ijazmuteeba@gmail.com', 'Student Management System');
        $mail->addAddress($to, $name);

        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $message;

        $mail->send();
        return true;

    } catch (Exception $e) {
        return false;
    }
}
?>
