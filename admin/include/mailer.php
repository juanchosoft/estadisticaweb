<?php
declare(strict_types=1);

use PHPMailer\PHPMailer\PHPMailer;

require_once __DIR__ . '/../../vendor/autoload.php';

class Mailer
{
  public static function sendSMTP(string $toEmail, string $toName, string $subject, string $html): array
  {
    $mail = new PHPMailer(true);

    try {
      $mail->isSMTP();
      $mail->Host       = MAIL_HOST;
      $mail->SMTPAuth   = true;
      $mail->Username   = MAIL_USERNAME;
      $mail->Password   = MAIL_PASSWORD;
      $mail->SMTPSecure = MAIL_SECURE;
      $mail->Port       = MAIL_PORT;

      $mail->CharSet = 'UTF-8';
      $mail->setFrom(MAIL_FROM_EMAIL, MAIL_FROM_NAME);
      $mail->addAddress($toEmail, $toName);

      $mail->isHTML(true);
      $mail->Subject = $subject;
      $mail->Body    = $html;

      $mail->send();
      return ['ok' => true];
    } catch (Throwable $e) {
      return ['ok' => false];
    }
  }
}
