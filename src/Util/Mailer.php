<?php
namespace App\Util;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

class Mailer{

public static function enviarEmail($corre,$remitente, $asunto, $mensaje )
{

     // Instantiation and passing `true` enables exceptions
     $mail = new PHPMailer(true);

     try {
         //Server settings
         $mail->SMTPDebug = SMTP::DEBUG_OFF;                      // Enable verbose debug output
         $mail->isSMTP();                                            // Send using SMTP
         $mail->Host       = 'smtp.gmail.com';                    // Set the SMTP server to send through
         $mail->SMTPAuth   = true;                                   // Enable SMTP authentication
         $mail->Username   = 'notificaciones@lux.edu.mx';                     // SMTP username
         $mail->Password   = 'inceptiolux';                               // SMTP password
         $mail->CharSet    = 'UTF-8';    // Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` encouraged
         $mail->SMTPSecure = 'tls';
         $mail->Port       = 587;                                    // TCP port to connect to, use 465 for `PHPMailer::ENCRYPTION_SMTPS` above

         //Recipients
         $mail->setFrom('notificaciones@lux.edu.mx', 'Clave Solicitud Ingreso');
         $mail->addAddress($corre, $remitente);     // Add a recipient
         

         // Content
         $mail->isHTML(true);                                  // Set email format to HTML
         $mail->Subject = $asunto;
         $mail->Body    = $mensaje;
        

         $envio=$mail->send();
         return $envio;
     } catch (Exception $e) {
        echo  "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
         return false;
     }
 }

}