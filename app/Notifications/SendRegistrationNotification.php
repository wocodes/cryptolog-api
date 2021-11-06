<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SendRegistrationNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public string $email;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($email)
    {
        $this->email = $email;
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
        $nameFromEmail = explode('@', $this->email)[0];
        $contents = [
            'AssetLog is a smart asset/investments service that helps you analyse your investment portfolio and share insights on how you can maximise your wealth.',
            'Our robust analytical AI tool stays ahead of time to assists you plan and prepare your future investments and avoidance of potential risks.',
            'As you\'re excited and await to get on board, we ask that you kindly fill this short survey by clicking the link below',
            '<a href="https://forms.gle/AdU2M6ZJFwYTh7V99" style="margin:10px 0;text-decoration:none;font-weight:bold;background-color:#2456b4;color:#fff;border-radius:5px;padding:10px;">Fill Survey</a>',
            "Please kindly change your password to a secure, secret value that is also memorable &#128578;"
        ];

        return (new MailMessage)
            ->subject("You're onto something amazing!")
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
