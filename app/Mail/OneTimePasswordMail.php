<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OneTimePasswordMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public string $otp)
    {
    }

    public function build(): self
    {
        return $this->subject('Your ATLAS One-Time Password')
            ->view('emails.otp')
            ->with([
                'otp' => $this->otp,
            ]);
    }
}
