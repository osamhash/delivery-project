<?php
namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

class ResetPasswordNotification extends ResetPassword
{
    public function toMail($notifiable): MailMessage
    {
        $frontendUrl = config('app.frontend_url', 'http://localhost:5173');
        $url = "{$frontendUrl}/forgot-password?token={$this->token}&email={$notifiable->email}";

        return (new MailMessage)
            ->subject('إعادة تعيين كلمة المرور — سوقبلس')
            ->greeting('مرحباً!')
            ->line('لقد طلبت إعادة تعيين كلمة مرور حسابك.')
            ->action('إعادة تعيين كلمة المرور', $url)
            ->line('صلاحية الرابط 60 دقيقة.')
            ->line('إذا لم تطلب ذلك، تجاهل هذا البريد.');
    }
}
