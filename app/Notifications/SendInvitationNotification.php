<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class SendInvitationNotification extends Notification
{
    use Queueable;

    private string $password;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($password)
    {
        $this->password = $password;
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
        $nameFromEmail = explode('@', $notifiable->email)[0];
        $contents = [
            'We welcome you on board to try out Assetlog.',
            'Thanks for joining us on this journey. 
            You are special and as such we desire to share with you this priviledge of being a part of our early app testers.',
            "We'd appreciate receiving your feedback as you use Assetlog, either via our in-app Chat, <a href=\"mailto:hello@assetlog.co\">email</a> or Whatsapp.",
            'Your ideas and opinions are highly welcomed and considered.',
            "Below are your login details:<br>
            <strong>URL:</strong> <a href='https://assetlog.co/login'>https://assetlog.co/login</a><br>
            <strong>Email:</strong> $notifiable->email<br>
            <strong>Temporary Password:</strong> $this->password
            ",
            "Please kindly change your password to a secure, secret value that is also memorable &#128578;"
        ];

        return (new MailMessage)
            ->subject("You've been invited!")
            ->view('mail.template', [
                'nameFromEmail' => $nameFromEmail,
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
