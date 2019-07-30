<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require ('./includes/configure.php');
require dirname(__FILE__).'/vendor/autoload.php';
/**
 * Created by PhpStorm.
 * User: yoh
 * Date: 2018/02/19
 * Time: 16:19
 */
//mb_language("Japanese");
//mb_internal_encoding("UTF-8");
//
//if(mb_send_mail('fourloop.jp@gmail.com','test','hoge')){
//    echo '送信成功';
//}else{
//    echo '送信失敗';
//}
$to_email_address = 'fourloop.jp@gmail.com';
$to_name = 'yoh';
$email_subject = 'TEST';
$email_text  = 'BODY';


$mail = new PHPMailer(true);                              // Passing `true` enables exceptions
try {
    mb_internal_encoding("UTF-8");
    //Server settings


    $mail->setLanguage('ja');
    $from_address = CERT_EMAIL_FROM_ADDR;
    $from_name = CERT_EMAIL_FROM_NAME;
    $mail->Encoding = CERT_EMAIL_ENCODING;
    $mail->SMTPDebug = 2;                                 // Enable verbose debug output
    $mail->isSMTP();                                      // Set mailer to use SMTP
    $mail->Host  = CERT_EMAIL_SMTP_HOST;  // Specify main and backup SMTP servers
    $mail->SMTPAuth = true;                               // Enable SMTP authentication
    $mail->Username = $from_address;                 // SMTP username
//      $mail->Password = 'bunch1234';
    $mail->Password = CERT_EMAIL_PASSWORD;
// SMTP password
    $mail->SMTPSecure = CERT_EMAIL_SMTP_SECURE;                            // Enable TLS encryption, `ssl` also accepted
    $mail->Port = CERT_EMAIL_PORT;                                    // TCP port to connect to(465,587)
    $mail->CharSet = CERT_EMAIL_CHARSET;
    //Recipients
    $mail->setFrom($from_address, $from_name);
    $mail->addAddress($to_email_address, $to_name);     // Add a recipient
//      $mail->addAddress('ellen@example.com');               // Name is optional
    $mail->addReplyTo($from_address,$from_name);
//      $mail->addCC('cc@example.com');
//      $mail->addBCC('bcc@example.com');

    //Attachments
//      $mail->addAttachment('/var/tmp/file.tar.gz');         // Add attachments
//      $mail->addAttachment('/tmp/image.jpg', 'new.jpg');    // Optional name

    //Content
    $mail->isHTML(false);                                  // Set email format to HTML
    $mail->Subject = mb_encode_mimeheader($email_subject);
//      $mail->Body = 'This is the HTML message body <b>in bold!</b>';
    $email_text = nl2br($email_text);
    $mail->Body = mb_convert_encoding($email_text,'UTF8');
    $mail->AltBody = mb_convert_encoding($email_text,'UTF8');
//      $mail->Body = mb_convert_encoding(mb_convert_kana($email_text, "KV"), 'JIS', 'UTF-8');;
//      $mail->AltBody = mb_convert_encoding(mb_convert_kana($email_text, "KV"), 'JIS', 'UTF-8');;

    $mail->send();
//      echo 'Message has been sent';
} catch (Exception $e) {
    echo 'Message could not be sent. Mailer Error: ', $mail->ErrorInfo;
}
