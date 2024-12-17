<?php

namespace Svr\Core\Extensions\Email;

use Exception;
use Illuminate\Support\Facades\Log;
use PHPMailer\PHPMailer\PHPMailer;

class SystemEmail
{
    /**
     * Отправить письмо
     * @param $email        - email пользователя
     * @param $title        - заголовок письма
     * @param $message      - тело письма
     *
     * @return bool
     */
    public static function sendEmailCustom($email, $title, $message): bool
    {

        $devops = env('ENVIRONMENT', 'PROD');
        $email_pattern = 'test@test.local';
        // если тестовая платформа
        if ($devops == 'TEST') {
            $email = env('MAIL_TEST', $email_pattern);
        }
        // если платформа для разработки
        if ($devops == 'DEVELOPER') {
            $email = env('MAIL_DEVELOPER', $email_pattern);
        }

        $mail = new PHPMailer(true);
        try {
            /* Email SMTP Settings */
            $mail->SMTPDebug = 0;
            $mail->isSMTP();
            $mail->Host = env('MAIL_HOST');
            $mail->SMTPAuth = true;
            $mail->Username = env('MAIL_USERNAME');
            $mail->Password = env('MAIL_PASSWORD');
            $mail->SMTPSecure = env('MAIL_ENCRYPTION');
            $mail->Port = env('MAIL_PORT');
            $mail->setFrom(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));
            $mail->addAddress($email);
            //$mail->addAddress($user_data['user_email']);
            $mail->isHTML(true);
            $mail->Subject = $title;
            $mail->Body    = $message;
            if( !$mail->send() ) {
                Log::error('Письмо не отправлено.', (array)$mail->ErrorInfo);
                return false;
            }
            else {
                return true;
            }
        } catch (Exception $e) {
            Log::error('Письмо не может быть отправлено.', (array)$e->getMessage());
            return false;
        }
    }
}
