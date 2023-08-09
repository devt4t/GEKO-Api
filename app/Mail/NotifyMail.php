<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class NotifyMail extends Mailable
{
    use Queueable, SerializesModels;
    
    public $GetScoopingDetail;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($GetScoopingDetail)
    {
        $this->GetScoopingDetail = $GetScoopingDetail;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('email_template');
    }
}
