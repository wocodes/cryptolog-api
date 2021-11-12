<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SendPasswordResetNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $token = $notifiable->password_reset_token;

        $contents = [
            "Hi, you initiated a password reset process on your account.",
            "Click the button below to proceed with your request",
            "<a href=\"https://assetlog.co/#/forgot-password?tk={$token}\" style=\"margin:20px 0;text-decoration:none;font-weight:bold;background-color:#2456b4;color:#fff;border-radius:5px;padding:10px;\">Reset Password</a>",
            "Please if you did not initiate this request, kindly ignore this message. Your account will still be safe as always",
        ];

        return (new MailMessage)
            ->subject("Password Reset Request")
            ->view('mail.template', [
                'contents' => $contents,
            ]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
