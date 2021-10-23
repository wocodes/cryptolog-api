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
        $personsNameFromEmail = explode('@', $this->email);
        return (new MailMessage)
                    ->subject("You're onto something amazing! Thanks for joining our waitlist")
                    ->greeting("Hello {$personsNameFromEmail[0]}")
                    ->line('AssetLog is a smart asset/investments tracker that  helps you analyse your investment portfolio and share insights on how you can maximise your wealth.')
                    ->line('Our robust analytical AI tool stays ahead of time to assists you plan and prepare your future investments and avoidance of potential risks.')
                    ->line('')
                    ->line('As you\'re excited and await to get on board, we ask that you kindly fill this short survey by clicking the link below')
                    ->action('Fill Survey', url('https://forms.gle/AdU2M6ZJFwYTh7V99'))
                    ->line('Thank you for joining our waitlist!');
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
