<?php


namespace App\Helper;


use App\Models\UserToken;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

class _EmailHelper
{
    public function __construct()
    {
    }

    public static function checkToken($token)
    {
        return UserToken::query()->where([
            'token' => $token,
        ])->first();

    }


    public static function generateToken($user)
    {
        $check = UserToken::query()->where([
            'user_id' => $user->id,
        ])->first();
        if ($check) {
            return $check->token;
        }
        $token = UserToken::query()->create([
            'token' => Str::slug(Hash::make($user->email)),
            'user_id' => $user->id,
        ]);
        return $token->token;
    }

    public static function setPassword($user, $data)
    {
        $token = self::generateToken($user);
        $data = [
            'id' => $user->id,
            'first_name' => $user->first_name,
            "token" => $token
        ];
        self::sendEmail($user, $data, 'user-password-email', 'Create your password');
    }

    public static function sendNotification($user, $data = [], $attachments = [])
    {
//        $token = $this->generateToken($user);
//        $data = [...$data, "token" => $token];
        $view = 'leave-notification-email';
        return self::sendEmail($user, $data, $view, 'Leave Request', $attachments);
    }

    public static function sendNotificationToUser($user, $data = [])
    {
//        $token = $this->generateToken($user);
//        $data = [...$data, "token" => $token];
        $view = 'leave-response-email';
        return self::sendEmail($user, $data, $view, 'Leave Request');
    }

    public static function sendEmail($user, $data, $view, $subject, $attachments = [])
    {
        try {
            $mail = new PHPMailer(true);
            // SMTP configurations
            $mail->isSMTP();
            $mail->Host = 'smtp.dreamhost.com';
            $mail->SMTPAuth = true;
            $mail->SMTPAutoTLS = true;
            $mail->Username = 'info@event.medcon.ae';
            $mail->Password = 'xnCNzMj92^LT';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port = 465;
            $mail->setFrom('info@medcon-me.com', 'Medcon');
            $mail->Sender = "info@medcon-me.com";
            $mail->ContentType = "text/html;charset=UTF-8\r\n";
            $mail->CharSet = 'UTF-8';
            $mail->Priority = 3;
            $mail->addCustomHeader("MIME-Version: 1.0\r\n");
            $mail->addCustomHeader("X-Mailer: PHP'" . phpversion() . "'\r\n");
            $mail->addAddress($user->email, $user->first_name);
            $mail->Subject = 'Medcon - ' . $subject;

            $mail->isHTML(true);
            // Email body content
            $mail->Body = view($view, $data)->render();
            foreach ($attachments as $attachment) {
                $mail->addAttachment($attachment);
            }
            // Send email
            $res = $mail->send();
            if ($res) {
                return true;
            }

        } catch (\Exception $e) {
            Log::error($e);
        }
        return false;
    }
}
