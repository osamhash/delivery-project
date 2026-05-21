<?php
namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

class ResetPasswordNotification extends ResetPassword
{
    public function toMail($notifiable): MailMessage
    {
        // رابط يفتح صفحة Vue وليس Laravel
        $frontendUrl = config('app.frontend_url', 'http://localhost:5173');
        $url = "{$frontendUrl}/forgot-password?token={$this->token}&email={$notifiable->getEmailForPasswordReset()}";

        return (new MailMessage)
            ->subject('🔑 إعادة تعيين كلمة المرور — سوقبلس')
            ->greeting("مرحباً {$notifiable->first_name}!")
            ->line('لقد تلقينا طلباً لإعادة تعيين كلمة مرور حسابك.')
            ->action('إعادة تعيين كلمة المرور', $url)
            ->line('⏰ هذا الرابط صالح لمدة 60 دقيقة فقط.')
            ->line('إذا لم تطلب ذلك، يمكنك تجاهل هذا البريد بأمان.')
            ->salutation('فريق سوقبلس 🛒');
    }
}
