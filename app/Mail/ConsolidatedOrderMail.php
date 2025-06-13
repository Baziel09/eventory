<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\View;

class ConsolidatedOrderMail extends Mailable
{
    use Queueable, SerializesModels;

    public Collection $orders;
    public string $emailSubject;
    public string $emailMessage;
    public ?string $pdfPath;

    public function __construct(Collection $orders, string $subject, string $message, ?string $pdfPath = null)
    {
        $this->orders = $orders;
        $this->emailSubject = $subject;
        $this->emailMessage = $message;
        $this->pdfPath = $pdfPath;
    }

    public function build()
    {
        // Load all necessary relationships for the template
        $this->orders->load([
            'supplier',
            'vendor',
            'user',
            'orderItems.item.unit'
        ]);

        return $this->subject($this->emailSubject)
            ->view('emails.consolidated-order', [
                'orders' => $this->orders,
                'emailMessage' => $this->emailMessage
            ])
            ->when($this->pdfPath && file_exists($this->pdfPath), function ($mail) {
                $mail->attach($this->pdfPath, [
                    'as' => "gecombineerde_bestelling.pdf",
                    'mime' => 'application/pdf',
                ]);
            });
    }
}