<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class SendVerificationEmail extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct()
    {

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
        $referralCode = $this->saveReferralCode($notifiable);
        $verificationCode = $this->saveVerificationCode($notifiable);

        $nameFromEmail = explode('@', $notifiable->email)[0];
        $contents = [
            'Welcome to Assetlog! We\'re excited to have you on board.',
            'Kindly verify your account by clicking the button below.',
            "<a href='https://assetlog.co/#/verify?code={$verificationCode}' style='margin:20px 0;text-decoration:none;font-weight:bold;background-color:#2456b4;color:#fff;border-radius:5px;padding:10px;'>Verify My Account</a>",

            '<br>',
            "Here is your referral link. Referrals are not compulsory but can help you receive bonuses in the future.<br>
            <strong>Referral URL:</strong> <a href='https://assetlog.co/#/register?ref={$referralCode}'>https://assetlog.co/#/register?ref={$referralCode}</a>",
            '<br>',
            'Lastly, we ask that you kindly fill this short survey by clicking the link below &#128578;',
            '<a href="https://forms.gle/AdU2M6ZJFwYTh7V99" style="text-decoration:none;font-weight:bold;color:#2456b4;">Fill Survey</a>',
        ];

        return (new MailMessage)
            ->subject("Verify your account on Assetlog!")
            ->view('mail.template', [
                'nameFromEmail' => $nameFromEmail,
                'contents' => $contents,
            ]);
    }


    private function saveReferralCode($user): string
    {
        $randomCode = strtoupper(substr(md5(time()), -6));
        $user->referral_code = $randomCode;
        $user->save();

        return $randomCode;
    }


    private function saveVerificationCode($user): string
    {
        $randomCode = md5(time());
        $user->verification_code = $randomCode;
        $user->save();

        return $randomCode;
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
