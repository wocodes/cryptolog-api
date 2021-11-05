<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

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

        return (new MailMessage)
            ->subject("Cheers! You've been invited!")
            ->greeting("Hi $nameFromEmail")
            ->line('We welcome you on board to try out Assetlog. A smart asset/investments tracker that  helps you analyse your investment portfolio and share insights on how you can maximise your wealth.')
            ->line('')
            ->line('Thanks for joining us on this journey. You are special and as such we desire to share with you this priviledge of being a part of our early app testers.')
            ->line("We'd so much appreciate your feedback as you use Assetlog, either via our in-app Chat, email or Whatsapp.")
            ->line('Your ideas and opinions are highly welcomed and considered.')
            ->line('')
            ->line('Below are your login details:')
            ->line('<strong>URL:</strong>: <a href="https://assetlog.co/login">https://assetlog.co/login</a>')
            ->line('<strong>Email:</strong>: ' . $notifiable->email)
            ->line('<strong>Temporary Password:</strong>: ' . $this->password)
            ->line('')
            ->line('Please kindly change your password to a secure, secret value that is also memorable &#128578;')
            ->line('&#10084;&#65039; From the AssetLog Team!');
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
